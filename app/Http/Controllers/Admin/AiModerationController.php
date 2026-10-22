<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Ai\Moderation\ModerationEvaluator;
use App\Services\Ai\Moderation\ModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * File de modération assistée : l'administrateur voit d'abord les contenus les plus risqués, avec
 * les raisons de l'IA, et tranche (« problème confirmé » ou « fausse alerte »). L'outil n'agit
 * jamais sur le contenu lui-même.
 */
class AiModerationController extends Controller
{
    public function index(Request $request, ModerationEvaluator $evaluator): View
    {
        $verdict = $request->query('verdict', 'flagged');   // flagged | likely | review | ok | all
        $status = $request->query('status', 'pending');     // pending | confirmed | dismissed | all
        $kind = $request->query('kind', 'all');             // text | image | all

        $q = DB::table('moderation_reports')->orderByDesc('score')->orderByDesc('updated_at');
        match ($verdict) {
            'flagged' => $q->where('verdict', '!=', 'ok'),
            'likely', 'review', 'ok' => $q->where('verdict', $verdict),
            default => null,
        };
        if (in_array($status, ['pending', 'confirmed', 'dismissed'], true)) {
            $q->where('status', $status);
        }
        if (in_array($kind, ['text', 'image'], true)) {
            $q->where('kind', $kind);
        }

        $reports = $q->paginate(25)->withQueryString();
        $reports->getCollection()->transform(function ($r) {
            $r->reasons = json_decode((string) $r->reasons, true) ?: [];
            $r->suggestions = json_decode((string) $r->suggestions, true) ?: [];

            return $r;
        });

        $counts = DB::table('moderation_reports')->where('verdict', '!=', 'ok')->select('status', DB::raw('COUNT(*) c'))->groupBy('status')->pluck('c', 'status');

        return view('admin.ai.moderation', [
            'reports' => $reports, 'verdict' => $verdict, 'status' => $status, 'kind' => $kind,
            'counts' => $counts, 'eval' => $evaluator->run(),
        ]);
    }

    public function decide(Request $request, int $report): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', 'in:confirmed,dismissed,pending']]);
        DB::table('moderation_reports')->where('id', $report)->update([
            'status' => $data['decision'],
            'decided_by' => $data['decision'] === 'pending' ? null : $request->user()->id,
            'decided_at' => $data['decision'] === 'pending' ? null : now(),
            'updated_at' => now(),
        ]);

        return back()->with('status', 'Décision enregistrée.');
    }

    public function scan(ModerationService $service): RedirectResponse
    {
        $n = $service->scanAll(true);

        return back()->with('status', sprintf(
            'Analyse terminée : %d articles, %d avis, %d prestataires, %d événements, %d photos.',
            $n['article'], $n['review'], $n['provider'], $n['event'], $n['image']
        ));
    }
}
