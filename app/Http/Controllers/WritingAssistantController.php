<?php

namespace App\Http\Controllers;

use App\Services\Ai\WritingAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Aide à la rédaction et à la traduction : suggestions uniquement, l'utilisateur relit et applique. */
class WritingAssistantController extends Controller
{
    public function run(Request $request, WritingAssistant $writer): JsonResponse
    {
        abort_unless(config('ai.enabled', true) && config('ai.writing.enabled', true), 404);
        $max = (int) config('ai.writing.max_chars', 20000);
        $data = $request->validate([
            'action' => ['required', 'in:summary,titles,seo,translate'],
            'lang' => ['nullable', 'in:fr,en'],
            'title' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:'.$max],
            'from' => ['nullable', 'in:fr,en'],
            'to' => ['nullable', 'in:fr,en'],
            'texts' => ['nullable', 'array'],
            'texts.*' => ['nullable', 'string', 'max:'.$max],
        ]);

        $userId = $request->user()->id;
        $today = DB::table('ai_writing_logs')->where('user_id', $userId)->where('created_at', '>=', now()->startOfDay())->count();
        if ($today >= (int) config('ai.writing.daily_cap', 100)) {
            return response()->json(['ok' => false, 'message' => 'Limite quotidienne atteinte : reprenez demain ou rédigez sans l\'assistant.'], 429);
        }

        $in = $data['content'] ?? '';
        $res = $writer->run($data['action'], $data);
        $out = $res['ok'] ? mb_strlen(json_encode($res['result'], JSON_UNESCAPED_UNICODE)) : 0;
        $logId = DB::table('ai_writing_logs')->insertGetId([
            'user_id' => $userId, 'action' => $data['action'], 'engine' => $res['engine'],
            'status' => $res['ok'] ? 'ok' : (in_array($res['engine'], ['unavailable', 'rejected'], true) ? $res['engine'] : 'rejected'),
            'chars_in' => mb_strlen($in) + array_sum(array_map('mb_strlen', array_map('strval', $data['texts'] ?? []))), 'chars_out' => $out, 'created_at' => now(),
        ]);

        return response()->json($res + ['log_id' => $logId], $res['ok'] ? 200 : 422);
    }

    public function applied(Request $request, int $log): JsonResponse
    {
        DB::table('ai_writing_logs')->where('id', $log)->where('user_id', $request->user()->id)->update(['applied' => true]);

        return response()->json(['ok' => true]);
    }
}
