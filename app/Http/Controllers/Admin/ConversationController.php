<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderConversationAttachment;
use App\Models\ProviderConversation;
use App\Models\ProviderConversationMessage;
use App\Models\User;
use App\Notifications\ConversationMessageNotification;
use App\Services\ConversationAttachmentSecurityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(
        private readonly ConversationAttachmentSecurityService $attachmentSecurity,
    ) {}

    public function index(Request $request): View
    {
        $status = (string) $request->get('status', '');
        $search = (string) $request->get('q', '');
        $priority = (string) $request->get('priority', '');
        $providers = Provider::query()
            ->with('user')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        $conversations = ProviderConversation::query()
            ->with(['provider', 'provider.user', 'assignedAdmin'])
            ->withCount([
                'messages as unread_count' => fn ($q) => $q
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', Auth::id()),
            ])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($priority !== '', fn ($q) => $q->where('priority', $priority))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('subject', 'like', "%{$search}%")
                        ->orWhereHas('provider', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.conversations.index', compact('conversations', 'status', 'search', 'priority', 'providers'));
    }

    public function poll(Request $request): JsonResponse
    {
        $status = (string) $request->get('status', '');
        $priority = (string) $request->get('priority', '');

        $items = ProviderConversation::query()
            ->withCount([
                'messages as unread_count' => fn ($q) => $q
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', Auth::id()),
            ])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($priority !== '', fn ($q) => $q->where('priority', $priority))
            ->orderByDesc('last_message_at')
            ->limit(50)
            ->get(['id', 'status', 'priority', 'last_message_at', 'last_message_preview'])
            ->map(fn ($row) => [
                'id' => $row->id,
                'status' => $row->status,
                'priority' => $row->priority,
                'unread_count' => (int) ($row->unread_count ?? 0),
                'last_message_preview' => (string) ($row->last_message_preview ?? ''),
                'last_message_at' => optional($row->last_message_at)->toIso8601String(),
            ]);

        return response()->json(['items' => $items]);
    }

    public function show(ProviderConversation $conversation): View
    {
        $conversation->load(['provider', 'provider.user', 'assignedAdmin']);
        $messages = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->oldest('id')
            ->paginate(60);
        $admins = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', Auth::id())
            ->update(['read_at' => now()]);

        return view('admin.conversations.show', compact('conversation', 'messages', 'admins'));
    }

    public function reply(Request $request, ProviderConversation $conversation): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xlsx,xls,txt', 'max:10240'],
        ]);

        $messageBody = trim($data['message']);
        DB::transaction(function () use ($conversation, $messageBody, $request) {
            $message = $conversation->messages()->create([
                'sender_id' => Auth::id(),
                'body' => $messageBody,
            ]);

            if ($request->hasFile('attachments')) {
                foreach ((array) $request->file('attachments') as $file) {
                    if (! $file) {
                        continue;
                    }
                    $this->storeAttachmentSecurely($message, $file);
                }
            }
        });

        $conversation->update([
            'status' => 'open',
            'last_message_at' => now(),
            'last_message_preview' => mb_substr($messageBody, 0, 180),
        ]);

        $providerUser = $conversation->provider?->user;
        if ($providerUser && (int) $providerUser->id !== (int) Auth::id()) {
            $providerUser->notify(new ConversationMessageNotification(
                $conversation,
                mb_substr($messageBody, 0, 180),
                route('provider.conversations.show', $conversation),
                'Nouveau message de l’administrateur'
            ));
        }

        return back()->with('success', 'Réponse envoyée.');
    }

    public function startDirectConversation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:4000'],
            'priority' => ['nullable', 'in:normal,urgent'],
        ]);

        $provider = Provider::query()
            ->with('user')
            ->whereKey($data['provider_id'])
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->firstOrFail();

        $subject = trim($data['subject']);
        $messageBody = trim($data['message']);
        $priority = (string) ($data['priority'] ?? 'normal');
        $senderId = (int) Auth::id();

        $conversation = DB::transaction(function () use ($provider, $subject, $messageBody, $priority, $senderId) {
            $conversation = ProviderConversation::query()
                ->where('provider_id', $provider->id)
                ->where('status', 'open')
                ->latest('last_message_at')
                ->first();

            if (! $conversation) {
                $conversation = ProviderConversation::create([
                    'provider_id' => $provider->id,
                    'assigned_admin_id' => $senderId,
                    'subject' => $subject,
                    'status' => 'open',
                    'priority' => $priority,
                    'last_message_at' => now(),
                    'last_message_preview' => mb_substr($messageBody, 0, 180),
                ]);
            } else {
                $conversation->update([
                    'assigned_admin_id' => $conversation->assigned_admin_id ?: $senderId,
                    'subject' => $conversation->subject ?: $subject,
                    'priority' => $priority,
                    'last_message_at' => now(),
                    'last_message_preview' => mb_substr($messageBody, 0, 180),
                ]);
            }

            $conversation->messages()->create([
                'sender_id' => $senderId,
                'body' => $messageBody,
            ]);

            return $conversation;
        });

        if ($provider->user && (int) $provider->user->id !== $senderId) {
            $provider->user->notify(new ConversationMessageNotification(
                $conversation,
                mb_substr($messageBody, 0, 180),
                route('provider.conversations.show', $conversation),
                'Nouveau message de l’administrateur'
            ));
        }

        return redirect()
            ->route('admin.conversations.show', $conversation)
            ->with('success', 'Message envoyé au prestataire.');
    }

    public function broadcastToAllProviders(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:4000'],
            'priority' => ['nullable', 'in:normal,urgent'],
            'only_without_open_conversation' => ['nullable', 'boolean'],
        ]);

        $subject = trim($data['subject']);
        $messageBody = trim($data['message']);
        $priority = (string) ($data['priority'] ?? 'normal');
        $onlyWithoutOpenConversation = (bool) ($data['only_without_open_conversation'] ?? false);
        $senderId = (int) Auth::id();
        $sentCount = 0;

        $providersQuery = Provider::query()
            ->with('user')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->orderBy('id');

        if ($onlyWithoutOpenConversation) {
            $providersQuery->whereDoesntHave('conversations', function ($q) {
                $q->where('status', 'open');
            });
        }

        $providersQuery
            ->chunkById(100, function ($providers) use ($subject, $messageBody, $priority, $senderId, &$sentCount): void {
                foreach ($providers as $provider) {
                    DB::transaction(function () use ($provider, $subject, $messageBody, $priority, $senderId, &$sentCount): void {
                        $conversation = ProviderConversation::create([
                            'provider_id' => $provider->id,
                            'assigned_admin_id' => $senderId,
                            'subject' => $subject,
                            'status' => 'open',
                            'priority' => $priority,
                            'last_message_at' => now(),
                            'last_message_preview' => mb_substr($messageBody, 0, 180),
                        ]);

                        $conversation->messages()->create([
                            'sender_id' => $senderId,
                            'body' => $messageBody,
                        ]);

                        $providerUser = $provider->user;
                        if ($providerUser) {
                            $providerUser->notify(new ConversationMessageNotification(
                                $conversation,
                                mb_substr($messageBody, 0, 180),
                                route('provider.conversations.show', $conversation),
                                'Message global de l’administrateur'
                            ));
                        }

                        $sentCount++;
                    });
                }
            });

        return back()->with('success', "Message envoyé à {$sentCount} prestataire(s).");
    }

    public function updateStatus(Request $request, ProviderConversation $conversation): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,closed'],
            'priority' => ['nullable', 'in:normal,urgent'],
            'assigned_admin_id' => ['nullable', 'exists:users,id'],
        ]);
        $updatePayload = [
            'status' => $data['status'],
            'priority' => $data['priority'] ?? $conversation->priority ?? 'normal',
            'assigned_admin_id' => $data['assigned_admin_id'] ?? null,
        ];

        $assignedAdmin = null;
        if (! empty($updatePayload['assigned_admin_id'])) {
            $assignedAdmin = User::query()
                ->where('role', 'admin')
                ->where('id', $updatePayload['assigned_admin_id'])
                ->first();
            $updatePayload['assigned_admin_id'] = $assignedAdmin?->id;
        }
        $conversation->update($updatePayload);

        $providerUser = $conversation->provider?->user;
        if ($providerUser) {
            $providerUser->notify(new ConversationMessageNotification(
                $conversation,
                'Statut/priorité de votre conversation mis à jour.',
                route('provider.conversations.show', $conversation),
                'Mise à jour de conversation'
            ));
        }

        return back()->with('success', 'Statut de conversation mis à jour.');
    }

    public function updateMessage(Request $request, ProviderConversation $conversation, ProviderConversationMessage $message): RedirectResponse
    {
        abort_unless((int) $message->conversation_id === (int) $conversation->id, 404);
        abort_unless((int) $message->sender_id === (int) Auth::id(), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:4000'],
        ]);

        $message->update(['body' => trim($data['body'])]);
        $this->refreshConversationSnapshot($conversation);

        return back()->with('success', 'Message modifié.');
    }

    public function deleteMessage(ProviderConversation $conversation, ProviderConversationMessage $message): RedirectResponse
    {
        abort_unless((int) $message->conversation_id === (int) $conversation->id, 404);
        abort_unless((int) $message->sender_id === (int) Auth::id(), 403);

        DB::transaction(function () use ($message): void {
            $message->loadMissing('attachments');
            foreach ($message->attachments as $attachment) {
                if (! empty($attachment->thumbnail_path)) {
                    Storage::disk('local')->delete($attachment->thumbnail_path);
                }
                Storage::disk('local')->delete($attachment->file_path);
                $attachment->delete();
            }
            $message->delete();
        });

        $this->refreshConversationSnapshot($conversation);

        return back()->with('success', 'Message supprimé.');
    }

    public function downloadAttachment(ProviderConversation $conversation, ProviderConversationAttachment $attachment)
    {
        abort_unless((int) $attachment->message?->conversation_id === (int) $conversation->id, 404);

        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return response()->download(Storage::disk('local')->path($attachment->file_path), $attachment->file_name);
    }

    public function previewAttachment(Request $request, ProviderConversation $conversation, ProviderConversationAttachment $attachment)
    {
        abort_unless((int) $attachment->message?->conversation_id === (int) $conversation->id, 404);
        abort_unless(str_starts_with((string) $attachment->mime_type, 'image/') || $attachment->mime_type === 'application/pdf', 404);

        $path = $attachment->file_path;
        if ($request->boolean('thumb') && str_starts_with((string) $attachment->mime_type, 'image/') && ! empty($attachment->thumbnail_path)) {
            $path = $attachment->thumbnail_path;
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => str_ends_with($path, '.jpg') ? 'image/jpeg' : ($attachment->mime_type ?: 'application/octet-stream'),
            'Content-Disposition' => 'inline; filename="'.$attachment->file_name.'"',
            'Cache-Control' => 'private, max-age=60',
        ]);
    }

    private function storeAttachmentSecurely(ProviderConversationMessage $message, UploadedFile $file): void
    {
        $this->attachmentSecurity->assertSafeUpload($file);
        $storedPath = $file->store('conversations/attachments', 'local');
        $absolutePath = Storage::disk('local')->path($storedPath);
        $scanResult = $this->attachmentSecurity->runAntivirusHook($absolutePath);
        if ($scanResult === 'infected') {
            Storage::disk('local')->delete($storedPath);
            abort(422, 'Un fichier joint a été bloqué par le contrôle antivirus.');
        }

        $ext = strtolower((string) $file->getClientOriginalExtension());
        $thumbAbsolute = $this->attachmentSecurity->createImageThumbnailIfPossible($absolutePath, $ext);
        $thumbnailPath = null;
        if ($thumbAbsolute && str_starts_with($thumbAbsolute, Storage::disk('local')->path(''))) {
            $thumbnailPath = ltrim(str_replace(Storage::disk('local')->path(''), '', $thumbAbsolute), '\\/');
        }

        $message->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'thumbnail_path' => $thumbnailPath,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => (int) $file->getSize(),
            'checksum_sha256' => hash_file('sha256', $absolutePath) ?: null,
            'scan_result' => $scanResult,
            'scanned_at' => now(),
        ]);
    }

    private function refreshConversationSnapshot(ProviderConversation $conversation): void
    {
        $lastMessage = $conversation->messages()->latest('id')->first();
        $conversation->update([
            'last_message_at' => $lastMessage?->created_at,
            'last_message_preview' => $lastMessage ? mb_substr((string) $lastMessage->body, 0, 180) : null,
            'status' => $lastMessage ? $conversation->status : 'closed',
        ]);
    }
}

