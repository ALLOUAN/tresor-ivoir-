<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConversationReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConversationReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'pending');

        $reports = ConversationReport::query()
            ->with(['reporter', 'reportedUser', 'conversation.provider'])
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending' => ConversationReport::where('status', 'pending')->count(),
            'reviewed' => ConversationReport::where('status', 'reviewed')->count(),
            'dismissed' => ConversationReport::where('status', 'dismissed')->count(),
        ];

        return view('admin.conversation-reports.index', compact('reports', 'status', 'counts'));
    }

    public function show(ConversationReport $report): View
    {
        $report->load(['reporter', 'reportedUser', 'reviewer', 'message', 'conversation.provider', 'conversation.client']);

        $messages = $report->conversation->messages()
            ->with(['sender', 'attachments'])
            ->oldest('id')
            ->paginate(50);

        return view('admin.conversation-reports.show', compact('report', 'messages'));
    }

    public function updateStatus(Request $request, ConversationReport $report): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:reviewed,dismissed'],
            'suspend_user' => ['nullable', 'boolean'],
        ]);

        $report->update([
            'status' => $data['status'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if ($data['status'] === 'reviewed' && $request->boolean('suspend_user')) {
            $report->reportedUser?->update(['is_active' => false]);
        }

        return back()->with('success', 'Signalement mis à jour.');
    }
}
