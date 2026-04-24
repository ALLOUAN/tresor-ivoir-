<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) > 200) {
            $q = substr($q, 0, 200);
        }

        $status = (string) $request->query('status', '');
        $allowedStatuses = array_keys(ContactMessage::statusOptions());
        if ($status !== '' && ! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $dateFrom = $this->sanitizeDate($request->query('date_from'));
        $dateTo = $this->sanitizeDate($request->query('date_to'));

        $base = ContactMessage::query();

        $stats = [
            'total' => (clone $base)->count(),
            'new' => (clone $base)->where('status', ContactMessage::STATUS_NEW)->count(),
            'in_progress' => (clone $base)->where('status', ContactMessage::STATUS_IN_PROGRESS)->count(),
            'done' => (clone $base)->where('status', ContactMessage::STATUS_DONE)->count(),
            'this_month' => (clone $base)->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $messages = ContactMessage::query()
            ->search($q !== '' ? $q : null)
            ->statusFilter($status !== '' ? $status : null)
            ->createdBetween(
                is_string($dateFrom) && $dateFrom !== '' ? $dateFrom : null,
                is_string($dateTo) && $dateTo !== '' ? $dateTo : null,
            )
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.system.contact-messages.index', compact(
            'messages',
            'stats',
            'q',
            'status',
            'dateFrom',
            'dateTo',
        ));
    }

    public function show(ContactMessage $contactMessage): View
    {
        if ($contactMessage->read_at === null) {
            $updates = ['read_at' => now()];
            if ($contactMessage->status === ContactMessage::STATUS_NEW) {
                $updates['status'] = ContactMessage::STATUS_IN_PROGRESS;
            }
            $contactMessage->update($updates);
            $contactMessage->refresh();
        }

        return view('admin.system.contact-messages.show', compact('contactMessage'));
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', [
                ContactMessage::STATUS_NEW,
                ContactMessage::STATUS_IN_PROGRESS,
                ContactMessage::STATUS_DONE,
            ])],
        ]);

        $contactMessage->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.administration.contact-messages.show', $contactMessage)
            ->with('success', 'Statut mis à jour.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()
            ->route('admin.administration.contact-messages.index')
            ->with('success', 'Message supprimé.');
    }

    public function export(Request $request): StreamedResponse
    {
        $format = $request->query('format', 'csv');
        if ($format !== 'csv') {
            abort(404);
        }

        $q = trim((string) $request->query('q', ''));
        if (strlen($q) > 200) {
            $q = substr($q, 0, 200);
        }

        $status = (string) $request->query('status', '');
        $allowedStatuses = array_keys(ContactMessage::statusOptions());
        if ($status !== '' && ! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $dateFrom = $this->sanitizeDate($request->query('date_from'));
        $dateTo = $this->sanitizeDate($request->query('date_to'));

        $rows = ContactMessage::query()
            ->search($q !== '' ? $q : null)
            ->statusFilter($status !== '' ? $status : null)
            ->createdBetween(
                is_string($dateFrom) && $dateFrom !== '' ? $dateFrom : null,
                is_string($dateTo) && $dateTo !== '' ? $dateTo : null,
            )
            ->orderByDesc('id')
            ->get();

        $filename = 'messages-contact-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['id', 'date', 'statut', 'nom', 'email', 'sujet', 'message'], ';');
            foreach ($rows as $m) {
                fputcsv($out, [
                    $m->id,
                    $m->created_at?->format('Y-m-d H:i:s'),
                    ContactMessage::STATUS_LABELS[$m->status] ?? $m->status,
                    $m->name,
                    $m->email,
                    $m->subject,
                    str_replace(["\r", "\n"], ' ', $m->message),
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function sanitizeDate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }
}
