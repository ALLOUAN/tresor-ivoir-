<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationAttachment;
use App\Models\ConversationMessage;
use App\Models\ConversationReport;
use App\Models\Provider;
use App\Models\User;
use App\Notifications\NewChatMessageNotification;
use App\Notifications\NewConversationReportNotification;
use App\Services\ConversationAttachmentSecurityService;
use App\Services\ConversationContentGuardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VisitorConversationController extends Controller
{
    public function __construct(
        private readonly ConversationAttachmentSecurityService $attachmentSecurity,
        private readonly ConversationContentGuardService $contentGuard,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $conversations = Conversation::query()
            ->where('client_id', Auth::id())
            ->with('provider')
            ->when($search !== '', fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('subject', 'like', "%{$search}%")
                    ->orWhereHas('provider', fn ($q3) => $q3->where('name', 'like', "%{$search}%"));
            }))
            ->withCount([
                'messages as unread_count' => fn ($q) => $q
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', Auth::id()),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('visitor.conversations.index', compact('conversations', 'search'));
    }

    public function poll(): JsonResponse
    {
        $items = Conversation::query()
            ->where('client_id', Auth::id())
            ->withCount([
                'messages as unread_count' => fn ($q) => $q
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', Auth::id()),
            ])
            ->orderByDesc('last_message_at')
            ->limit(30)
            ->get(['id', 'status', 'last_message_at', 'last_message_preview'])
            ->map(fn ($row) => [
                'id' => $row->id,
                'status' => $row->status,
                'unread_count' => (int) ($row->unread_count ?? 0),
                'last_message_preview' => (string) ($row->last_message_preview ?? ''),
                'last_message_at' => optional($row->last_message_at)->toIso8601String(),
            ]);

        return response()->json(['items' => $items]);
    }

    public function show(Conversation $conversation): View
    {
        abort_unless((int) $conversation->client_id === (int) Auth::id(), 403);

        $conversation->load(['provider.user']);
        $messages = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->oldest('id')
            ->paginate(50);

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', Auth::id())
            ->update(['read_at' => now()]);

        return view('visitor.conversations.show', compact('conversation', 'messages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:4000'],
            'context_type' => ['nullable', 'string', 'in:reservation'],
            'context_id' => ['nullable', 'integer'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xlsx,xls,txt', 'max:10240'],
        ]);

        $provider = Provider::with('user')->findOrFail($data['provider_id']);
        $messageBody = trim($data['message']);
        $scan = $this->contentGuard->scan($messageBody);
        $clientId = (int) Auth::id();

        $conversation = DB::transaction(function () use ($provider, $data, $messageBody, $scan, $clientId, $request) {
            $conversation = Conversation::query()
                ->where('client_id', $clientId)
                ->where('provider_id', $provider->id)
                ->where('status', 'open')
                ->latest('last_message_at')
                ->first();

            if (! $conversation) {
                $conversation = Conversation::create([
                    'client_id' => $clientId,
                    'provider_id' => $provider->id,
                    'context_type' => $data['context_type'] ?? null,
                    'context_id' => $data['context_id'] ?? null,
                    'subject' => $data['subject'] ?? null,
                    'status' => 'open',
                    'last_message_at' => now(),
                    'last_message_preview' => mb_substr($messageBody, 0, 180),
                ]);
            } elseif ($conversation->isBlocked()) {
                abort(422, 'Cette conversation est bloquée.');
            } else {
                $conversation->update([
                    'subject' => $conversation->subject ?: ($data['subject'] ?? null),
                    'context_type' => $conversation->context_type ?: ($data['context_type'] ?? null),
                    'context_id' => $conversation->context_id ?: ($data['context_id'] ?? null),
                    'last_message_at' => now(),
                    'last_message_preview' => mb_substr($messageBody, 0, 180),
                ]);
            }

            $message = $conversation->messages()->create([
                'sender_id' => $clientId,
                'body' => $messageBody,
                'is_flagged' => $scan['flagged'],
                'flagged_reason' => $scan['reasons'] ? implode(',', $scan['reasons']) : null,
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

        if ($provider->user) {
            $provider->user->notify(new NewChatMessageNotification(
                $conversation,
                mb_substr($messageBody, 0, 180),
                route('provider.client-conversations.show', $conversation),
                'Nouveau message client'
            ));
        }

        return redirect()
            ->route('visitor.conversations.show', $conversation)
            ->with('success', 'Message envoyé.')
            ->with('chat_warning', $this->contentGuard->warningMessageFor($scan['reasons']));
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        abort_unless((int) $conversation->client_id === (int) Auth::id(), 403);
        abort_if($conversation->isBlocked(), 422, 'Cette conversation est bloquée.');

        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xlsx,xls,txt', 'max:10240'],
        ]);

        $messageBody = trim($data['message']);
        $scan = $this->contentGuard->scan($messageBody);

        DB::transaction(function () use ($conversation, $messageBody, $scan, $request) {
            $message = $conversation->messages()->create([
                'sender_id' => Auth::id(),
                'body' => $messageBody,
                'is_flagged' => $scan['flagged'],
                'flagged_reason' => $scan['reasons'] ? implode(',', $scan['reasons']) : null,
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

        $conversation->loadMissing('provider.user');
        if ($conversation->provider?->user) {
            $conversation->provider->user->notify(new NewChatMessageNotification(
                $conversation,
                mb_substr($messageBody, 0, 180),
                route('provider.client-conversations.show', $conversation),
                'Nouveau message client'
            ));
        }

        return back()
            ->with('success', 'Message envoyé.')
            ->with('chat_warning', $this->contentGuard->warningMessageFor($scan['reasons']));
    }

    public function report(Request $request, Conversation $conversation): RedirectResponse
    {
        abort_unless((int) $conversation->client_id === (int) Auth::id(), 403);

        $data = $request->validate([
            'message_id' => ['nullable', 'integer', 'exists:conversation_messages,id'],
            'reason' => ['required', 'string', 'in:spam,harcelement,contenu_interdit,contournement_paiement,autre'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        $conversation->loadMissing('provider.user');
        $reportedUserId = $conversation->provider?->user?->id;
        abort_unless($reportedUserId, 404);

        $report = ConversationReport::create([
            'conversation_id' => $conversation->id,
            'message_id' => $data['message_id'] ?? null,
            'reported_by' => Auth::id(),
            'reported_user_id' => $reportedUserId,
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'status' => 'pending',
        ]);

        foreach (User::where('role', 'admin')->where('is_active', true)->get() as $admin) {
            $admin->notify(new NewConversationReportNotification($report));
        }

        return back()->with('success', 'Signalement envoyé à l\'équipe de modération.');
    }

    public function block(Conversation $conversation): RedirectResponse
    {
        abort_unless((int) $conversation->client_id === (int) Auth::id(), 403);

        if (! $conversation->client_blocked_at) {
            $conversation->update(['client_blocked_at' => now()]);
        }

        return back()->with('success', 'Utilisateur bloqué. Vous ne recevrez plus de messages de sa part.');
    }

    public function downloadAttachment(Conversation $conversation, ConversationAttachment $attachment)
    {
        abort_unless((int) $conversation->client_id === (int) Auth::id(), 403);
        abort_unless((int) $attachment->message?->conversation_id === (int) $conversation->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return response()->download(Storage::disk('local')->path($attachment->file_path), $attachment->file_name);
    }

    public function previewAttachment(Request $request, Conversation $conversation, ConversationAttachment $attachment)
    {
        abort_unless((int) $conversation->client_id === (int) Auth::id(), 403);
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

    private function storeAttachmentSecurely(ConversationMessage $message, UploadedFile $file): void
    {
        $this->attachmentSecurity->assertSafeUpload($file);
        $storedPath = $file->store('chat/attachments', 'local');
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
}
