<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterCampaignMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterManagementController extends Controller
{
    public function index(Request $request): View
    {
        $hasSubscribers = Schema::hasTable('newsletter_subscribers');
        $hasCampaigns = Schema::hasTable('newsletter_campaigns');

        $activeCount = $hasSubscribers
            ? NewsletterSubscriber::query()->where('status', 'active')->count()
            : 0;

        $subscriberStats = [
            'total' => 0,
            'active' => 0,
            'pending' => 0,
            'unsubscribed' => 0,
            'bounced' => 0,
        ];

        $subscribers = null;
        if ($hasSubscribers) {
            $subscriberStats = [
                'total' => NewsletterSubscriber::query()->count(),
                'active' => NewsletterSubscriber::query()->where('status', 'active')->count(),
                'pending' => NewsletterSubscriber::query()->where('status', 'pending')->count(),
                'unsubscribed' => NewsletterSubscriber::query()->where('status', 'unsubscribed')->count(),
                'bounced' => NewsletterSubscriber::query()->where('status', 'bounced')->count(),
            ];

            $q = trim((string) $request->query('q', ''));
            if (strlen($q) > 200) {
                $q = substr($q, 0, 200);
            }
            $status = (string) $request->query('status', '');
            $allowed = ['active', 'pending', 'unsubscribed', 'bounced'];
            if ($status !== '' && ! in_array($status, $allowed, true)) {
                $status = '';
            }

            $subQuery = NewsletterSubscriber::query()
                ->with('user:id,first_name,last_name,email')
                ->orderByDesc('id');

            if ($q !== '') {
                $subQuery->where('email', 'like', '%'.$q.'%');
            }
            if ($status !== '') {
                $subQuery->where('status', $status);
            }

            $subscribers = $subQuery->paginate(20)->withQueryString();
        }

        $campaigns = $hasCampaigns
            ? NewsletterCampaign::query()
                ->with('creator:id,first_name,last_name,email')
                ->latest()
                ->limit(30)
                ->get()
            : collect();

        return view('admin.newsletter.index', [
            'hasSubscribers' => $hasSubscribers,
            'hasCampaigns' => $hasCampaigns,
            'activeCount' => $activeCount,
            'subscriberStats' => $subscriberStats,
            'subscribers' => $subscribers,
            'q' => $request->query('q', ''),
            'statusFilter' => $request->query('status', ''),
            'campaigns' => $campaigns,
        ]);
    }

    public function individualMessageForm(NewsletterSubscriber $subscriber): View|RedirectResponse
    {
        if (! Schema::hasTable('newsletter_subscribers') || ! Schema::hasTable('newsletter_campaigns')) {
            abort(404);
        }

        if ($subscriber->status !== 'active') {
            return redirect()
                ->route('admin.newsletter.index')
                ->withErrors(['send' => 'L’envoi individuel n’est disponible que pour les abonnés au statut « actif ».']);
        }

        $subscriber->load('user:id,first_name,last_name,email');

        return view('admin.newsletter.individual', [
            'subscriber' => $subscriber,
        ]);
    }

    public function sendIndividual(Request $request, NewsletterSubscriber $subscriber): RedirectResponse
    {
        if (! Schema::hasTable('newsletter_subscribers') || ! Schema::hasTable('newsletter_campaigns')) {
            return redirect()
                ->route('admin.newsletter.index')
                ->withErrors(['send' => 'Les tables newsletter ne sont pas disponibles. Exécutez les migrations.']);
        }

        if ($subscriber->status !== 'active') {
            return redirect()
                ->route('admin.newsletter.index')
                ->withErrors(['send' => 'Cet abonné n’est pas actif : envoi annulé.']);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subject_fr' => ['required', 'string', 'max:255'],
            'subject_en' => ['nullable', 'string', 'max:255'],
            'content_fr' => ['required', 'string'],
            'content_en' => ['nullable', 'string'],
            'content_format' => ['required', 'in:plain,html'],
        ], [], [
            'subject_fr' => 'objet (FR)',
            'content_fr' => 'message',
        ]);

        [$contentFr, $contentEn] = $this->buildNewsletterBodies($validated);

        $title = filled($validated['title'] ?? null)
            ? $validated['title']
            : 'Individuel — '.$subscriber->email;

        $campaign = NewsletterCampaign::query()->create([
            'title' => $title,
            'subject_fr' => $validated['subject_fr'],
            'subject_en' => $validated['subject_en'] ?? null,
            'content_fr' => $contentFr,
            'content_en' => $contentEn,
            'type' => 'onboarding',
            'status' => 'sending',
            'created_by' => $request->user()->id,
        ]);

        try {
            Mail::to($subscriber->email)->send(new NewsletterCampaignMail($campaign, $subscriber));
            $campaign->update([
                'status' => 'sent',
                'sent_at' => now(),
                'recipients_count' => 1,
            ]);

            return redirect()
                ->route('admin.newsletter.subscribers.message', $subscriber)
                ->with('success', 'Message envoyé à '.$subscriber->email.'.');
        } catch (\Throwable $e) {
            Log::error('Newsletter individual send failed', [
                'email' => $subscriber->email,
                'campaign_id' => $campaign->id,
                'message' => $e->getMessage(),
            ]);
            $campaign->update([
                'status' => 'cancelled',
                'sent_at' => null,
                'recipients_count' => 0,
            ]);

            return redirect()
                ->route('admin.newsletter.subscribers.message', $subscriber)
                ->withInput()
                ->withErrors(['send' => 'Échec de l’envoi. Vérifiez la configuration e-mail (.env) et les journaux.']);
        }
    }

    public function exportSubscribers(Request $request): StreamedResponse
    {
        if (! Schema::hasTable('newsletter_subscribers')) {
            abort(404);
        }

        $q = trim((string) $request->query('q', ''));
        if (strlen($q) > 200) {
            $q = substr($q, 0, 200);
        }
        $status = (string) $request->query('status', '');
        $allowed = ['active', 'pending', 'unsubscribed', 'bounced'];
        if ($status !== '' && ! in_array($status, $allowed, true)) {
            $status = '';
        }

        $rows = NewsletterSubscriber::query()
            ->orderByDesc('id')
            ->when($q !== '', fn ($b) => $b->where('email', 'like', '%'.$q.'%'))
            ->when($status !== '', fn ($b) => $b->where('status', $status))
            ->get();

        $filename = 'newsletter-abonnes-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['id', 'email', 'prenom', 'statut', 'locale', 'source', 'compte_utilisateur', 'inscrit_le', 'confirmé_le'], ';');
            foreach ($rows as $s) {
                fputcsv($out, [
                    $s->id,
                    $s->email,
                    $s->first_name ?? '',
                    $s->status,
                    $s->locale,
                    $s->source ?? '',
                    $s->user_id ? (string) $s->user_id : '',
                    $s->created_at?->format('Y-m-d H:i:s') ?? '',
                    $s->confirmed_at?->format('Y-m-d H:i:s') ?? '',
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('newsletter_subscribers') || ! Schema::hasTable('newsletter_campaigns')) {
            return redirect()
                ->route('admin.newsletter.index')
                ->withErrors(['send' => 'Les tables newsletter ne sont pas disponibles. Exécutez les migrations.']);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject_fr' => ['required', 'string', 'max:255'],
            'subject_en' => ['nullable', 'string', 'max:255'],
            'content_fr' => ['required', 'string'],
            'content_en' => ['nullable', 'string'],
            'content_format' => ['required', 'in:plain,html'],
        ], [], [
            'title' => 'titre interne',
            'subject_fr' => 'objet (FR)',
            'content_fr' => 'message',
        ]);

        [$contentFr, $contentEn] = $this->buildNewsletterBodies($validated);

        $subscribers = NewsletterSubscriber::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        if ($subscribers->isEmpty()) {
            return redirect()
                ->route('admin.newsletter.index')
                ->withInput()
                ->withErrors(['send' => 'Aucun abonné actif : impossible d’envoyer un message.']);
        }

        $campaign = NewsletterCampaign::query()->create([
            'title' => $validated['title'],
            'subject_fr' => $validated['subject_fr'],
            'subject_en' => $validated['subject_en'] ?? null,
            'content_fr' => $contentFr,
            'content_en' => $contentEn,
            'type' => 'promo',
            'status' => 'sending',
            'created_by' => $request->user()->id,
        ]);

        $sent = 0;
        $failed = 0;

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new NewsletterCampaignMail($campaign, $subscriber));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Newsletter send failed', [
                    'email' => $subscriber->email,
                    'campaign_id' => $campaign->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $message = $sent > 0
            ? "Message envoyé à {$sent} abonné(s)".($failed > 0 ? " ({$failed} échec(s))." : '.')
            : 'Aucun envoi réussi. Vérifiez la configuration e-mail (.env).';

        if ($sent === 0) {
            $campaign->update([
                'status' => 'cancelled',
                'sent_at' => null,
                'recipients_count' => 0,
            ]);

            return redirect()
                ->route('admin.newsletter.index')
                ->withErrors(['send' => $message]);
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'recipients_count' => $sent,
        ]);

        return redirect()
            ->route('admin.newsletter.index')
            ->with('success', $message);
    }

    /**
     * @param  array{content_fr: string, content_en?: string|null, content_format: string}  $validated
     * @return array{0: string, 1: string|null}
     */
    private function buildNewsletterBodies(array $validated): array
    {
        $plainWrap = static function (string $text): string {
            return '<div style="font-size:15px;line-height:1.65;color:#cbd5e1;">'.nl2br(e($text)).'</div>';
        };

        $contentFr = $validated['content_format'] === 'plain'
            ? $plainWrap($validated['content_fr'])
            : $validated['content_fr'];

        $contentEn = null;
        if (filled($validated['content_en'] ?? null)) {
            $contentEn = $validated['content_format'] === 'plain'
                ? $plainWrap($validated['content_en'])
                : $validated['content_en'];
        }

        return [$contentFr, $contentEn];
    }
}
