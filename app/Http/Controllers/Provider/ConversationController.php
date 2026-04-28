<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
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

    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $provider = $user->providers()->firstOrFail();

        $conversations = ProviderConversation::query()
            ->where('provider_id', $provider->id)
            ->with('assignedAdmin')
            ->withCount([
                'messages as unread_count' => fn ($q) => $q
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', Auth::id()),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('provider.conversations.index', compact('conversations'));
    }

    public function poll(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $provider = $user->providers()->firstOrFail();

        $items = ProviderConversation::query()
            ->where('provider_id', $provider->id)
            ->withCount([
                'messages as unread_count' => fn ($q) => $q
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', Auth::id()),
            ])
            ->orderByDesc('last_message_at')
            ->limit(30)
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $provider = $user->providers()->firstOrFail();
        abort_unless((int) $conversation->provider_id === (int) $provider->id, 403);

        $conversation->load(['provider.user', 'assignedAdmin']);
        $messages = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->oldest('id')
            ->paginate(50);

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', Auth::id())
            ->update(['read_at' => now()]);

        return view('provider.conversations.show', compact('conversation', 'messages'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $provider = $user->providers()->firstOrFail();

        $data = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:4000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xlsx,xls,txt', 'max:10240'],
        ]);

        $messageBody = trim($data['message']);
        $conversation = DB::transaction(function () use ($provider, $data, $messageBody, $request) {
            $conversation = ProviderConversation::create([
                'provider_id' => $provider->id,
                'subject' => $data['subject'] ?: 'Nouvelle conversation',
                'status' => 'open',
                'priority' => 'normal',
                'last_message_at' => now(),
                'last_message_preview' => mb_substr($messageBody, 0, 180),
            ]);

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

            return $conversation;
        });

        $admins = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->where('id', '!=', Auth::id())
            ->get();
        foreach ($admins as $admin) {
            $admin->notify(new ConversationMessageNotification(
                $conversation,
                mb_substr($messageBody, 0, 180),
                route('admin.conversations.show', $conversation),
                'Nouveau message prestataire'
            ));
        }

        return redirect()
            ->route('provider.conversations.show', $conversation)
            ->with('success', 'Conversation créée.');
    }

    public function reply(Request $request, ProviderConversation $conversation): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $provider = $user->providers()->firstOrFail();
        abort_unless((int) $conversation->provider_id === (int) $provider->id, 403);

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

        $adminsQuery = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->where('id', '!=', Auth::id());
        if ($conversation->assigned_admin_id) {
            $adminsQuery->where('id', $conversation->assigned_admin_id);
        }
        foreach ($adminsQuery->get() as $admin) {
            $admin->notify(new ConversationMessageNotification(
                $conversation,
                mb_substr($messageBody, 0, 180),
                route('admin.conversations.show', $conversation),
                'Réponse prestataire'
            ));
        }

        return back()->with('success', 'Message envoyé.');
    }

    public function updateMessage(Request $request, ProviderConversation $conversation, ProviderConversationMessage $message): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $provider = $user->providers()->firstOrFail();
        abort_unless((int) $conversation->provider_id === (int) $provider->id, 403);
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $provider = $user->providers()->firstOrFail();
        abort_unless((int) $conversation->provider_id === (int) $provider->id, 403);
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $provider = $user->providers()->firstOrFail();
        abort_unless((int) $conversation->provider_id === (int) $provider->id, 403);
        abort_unless((int) $attachment->message?->conversation_id === (int) $conversation->id, 404);

        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return response()->download(Storage::disk('local')->path($attachment->file_path), $attachment->file_name);
    }

    public function previewAttachment(Request $request, ProviderConversation $conversation, ProviderConversationAttachment $attachment)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $provider = $user->providers()->firstOrFail();
        abort_unless((int) $conversation->provider_id === (int) $provider->id, 403);
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

