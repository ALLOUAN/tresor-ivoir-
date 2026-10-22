<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripPlan;
use App\Services\Ai\Catalog;
use App\Services\Ai\CollabModel;
use App\Services\Ai\InteractionStore;
use App\Services\Ai\PlannerEvaluator;
use App\Services\Ai\RecommendationEvaluator;
use App\Services\Ai\Analytics\AnalyticsEvaluator;
use App\Services\Ai\ChatEvaluator;
use App\Services\Ai\SearchEvaluator;
use App\Services\Ai\WritingEvaluator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Tableau de bord d'évaluation des modules IA (administrateur). */
class AiInsightsController extends Controller
{
    /** @return array<string,mixed> usage réel de l'assistant */
    private function chatLogs(): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('ai_chat_logs')) {
            return ['total' => 0];
        }
        $q = DB::table('ai_chat_logs');
        $total = (clone $q)->count();
        $by = (clone $q)->select('status', DB::raw('COUNT(*) c'))->groupBy('status')->pluck('c', 'status');
        $rated = (clone $q)->whereNotNull('rating')->count();

        return [
            'total' => $total, 'answered' => (int) ($by['answered'] ?? 0), 'no_source' => (int) ($by['no_source'] ?? 0),
            'rated' => $rated, 'useful' => (clone $q)->where('rating', 1)->count(),
            'top_unanswered' => (clone $q)->where('status', 'no_source')->select('question', DB::raw('COUNT(*) n'))->groupBy('question')->orderByDesc('n')->limit(8)->get(),
        ];
    }

    /** Aperçu complet (niveau Gold) de l'analyse d'un prestataire. */
    public function provider(int $provider, \App\Services\Ai\Analytics\InsightsReport $reports): View
    {
        $row = DB::table('providers')->where('id', $provider)->whereNull('deleted_at')->firstOrFail();

        return view('provider.insights', ['report' => $reports->build($row, 3), 'adminPreview' => true]);
    }

    public function index(Request $request, Catalog $catalog, InteractionStore $store, RecommendationEvaluator $recoEval, PlannerEvaluator $plannerEval, SearchEvaluator $searchEval, ChatEvaluator $chatEval, WritingEvaluator $writingEval, AnalyticsEvaluator $analyticsEval): View
    {
        $k = max(1, min(10, (int) $request->query('k', 5)));
        $matrix = $store->matrix();
        $model = new CollabModel($matrix);
        $items = $catalog->items();

        $byType = [];
        foreach ($items as $i) {
            $byType[$i->typeLabel] = ($byType[$i->typeLabel] ?? 0) + 1;
        }
        arsort($byType);

        $top = DB::table('ai_interactions')->select('item_type', 'item_id', DB::raw('COUNT(*) as n'))
            ->groupBy('item_type', 'item_id')->orderByDesc('n')->limit(8)->get()
            ->map(fn ($r) => ['title' => $items[$r->item_type.':'.$r->item_id]->title ?? ($r->item_type.' #'.$r->item_id), 'n' => $r->n]);

        return view('admin.ai.index', [
            'k' => $k,
            'stats' => [
                'catalog' => count($items),
                'by_type' => $byType,
                'interactions' => DB::table('ai_interactions')->where('is_simulated', false)->count(),
                'simulated' => DB::table('ai_interactions')->where('is_simulated', true)->count(),
                'actors' => count($matrix),
                'active_actors' => $model->activeActors(),
                'profiles' => DB::table('ai_profiles')->count(),
                'trips' => TripPlan::count(),
                'trips_7d' => TripPlan::where('created_at', '>=', now()->subDays(7))->count(),
                'llm' => config('ai.llm.driver').(config('ai.llm.driver') === 'anthropic' && ! config('ai.llm.api_key') ? ' (clé absente : rédaction par modèle de texte)' : ''),
            ],
            'top' => $top,
            'reco' => $recoEval->run($k),
            'planner' => $plannerEval->run(),
            'chat' => [
                'main' => $chatEval->run(false), 'validation' => $chatEval->run(true), 'guards' => $chatEval->guardrails(),
                'logs' => $this->chatLogs(),
            ],
            'writing' => $writingEval->run(),
            'analytics' => $analyticsEval->run() + ['providers_list' => DB::table('providers')->where('status', 'active')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name'])],
            'search' => $searchEval->run(3, false),
            'searchHeldOut' => $searchEval->run(3, true),
            'noResult' => DB::table('ai_search_logs')->where('smart_results', 0)->where('keyword_results', 0)
                ->select('query', DB::raw('COUNT(*) as n'))->groupBy('query')->orderByDesc('n')->limit(8)->get(),
        ]);
    }
}
