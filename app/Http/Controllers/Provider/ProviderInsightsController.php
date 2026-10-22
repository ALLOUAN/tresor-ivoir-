<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\Ai\Analytics\InsightsReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Analyse avancée (prévision, comparaison à la catégorie, suggestions) : contenu des forfaits Silver et Gold. */
class ProviderInsightsController extends Controller
{
    public function show(Request $request, InsightsReport $reports): View
    {
        abort_unless(config('ai.enabled', true), 404);
        $provider = DB::table('providers')->where('user_id', $request->user()->id)->whereNull('deleted_at')->first();

        return view('provider.insights', ['report' => $provider ? $reports->build($provider) : null, 'adminPreview' => false]);
    }

    /** Export CSV de la prévision et de la comparaison (forfait Gold). */
    public function export(Request $request, InsightsReport $reports): StreamedResponse
    {
        abort_unless(config('ai.enabled', true), 404);
        $provider = DB::table('providers')->where('user_id', $request->user()->id)->whereNull('deleted_at')->first();
        abort_unless($provider, 404);
        $r = $reports->build($provider);
        abort_unless($r['rank'] >= 3, 403, 'L\'export est réservé au forfait Gold.');

        return $this->csv($r, 'analyse-'.$provider->slug.'-'.now()->format('Ymd').'.csv');
    }

    public function csv(array $r, string $name): StreamedResponse
    {
        return response()->streamDownload(function () use ($r) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Prévision de fréquentation', 'Vues prévues', 'Borne basse (80 %)', 'Borne haute (80 %)'], ';');
            foreach ($r['forecast']['forecast'] ?? [] as $f) {
                fputcsv($out, [$f['date'], $f['yhat'], $f['lo'], $f['hi']], ';');
            }
            fputcsv($out, [], ';');
            fputcsv($out, ['Comparaison : '.($r['benchmark']['scope_label'] ?? ''), 'Votre fiche', 'Médiane', 'Quart supérieur', 'Rang percentile'], ';');
            foreach ($r['benchmark']['rows'] ?? [] as $row) {
                fputcsv($out, [$row['label'], $row['mine'], $row['median'], $row['top_quartile'], $row['percentile']], ';');
            }
            fputcsv($out, [], ';');
            fputcsv($out, ['Suggestions', 'Priorité', 'Constat', 'Action'], ';');
            foreach ($r['advice'] as $a) {
                fputcsv($out, [$a['title'], $a['impact'], $a['evidence'], $a['action']], ';');
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
