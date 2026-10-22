<?php

namespace App\Http\Controllers;

use App\Services\Ai\ChatAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Assistant conversationnel du site (JSON, utilisé par le widget et l'application mobile). */
class ChatAssistantController extends Controller
{
    public function ask(Request $request, ChatAssistant $assistant): JsonResponse
    {
        abort_unless(config('ai.enabled', true) && config('ai.chat.enabled', true), 404);
        $data = $request->validate([
            'message' => ['required', 'string', 'max:'.config('ai.chat.max_question', 300)],
            'history' => ['nullable', 'array', 'max:6'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.text' => ['required_with:history', 'string', 'max:600'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $position = isset($data['lat'], $data['lng']) ? [(float) $data['lat'], (float) $data['lng']] : null;
        $r = $assistant->answer($data['message'], array_slice($data['history'] ?? [], -4), $position);
        $id = $assistant->log($data['message'], $r);

        return response()->json([
            'id' => $id,
            'status' => $r['status'],
            'answer' => $r['answer'],
            'sources' => $r['sources'],
            'handoff' => $r['handoff'],
            'suggestions' => $r['suggestions'],
            'lang' => $r['lang'],
            'engine' => $r['engine'],
            'maps_widget' => $r['maps_widget'] ?? null,
        ]);
    }

    public function feedback(Request $request): JsonResponse
    {
        abort_unless(config('ai.enabled', true) && config('ai.chat.enabled', true), 404);
        $data = $request->validate(['id' => ['required', 'integer'], 'rating' => ['required', 'in:1,-1']]);
        DB::table('ai_chat_logs')->where('id', $data['id'])->update(['rating' => (int) $data['rating']]);

        return response()->json(['ok' => true]);
    }
}
