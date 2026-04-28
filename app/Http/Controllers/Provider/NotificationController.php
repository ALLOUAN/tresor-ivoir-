<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(20);

        return view('provider.notifications.index', compact('notifications'));
    }

    public function markAllRead(): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    public function markRead(DatabaseNotification $notification): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless((string) $notification->notifiable_id === (string) $user->id, 403);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        $targetUrl = (string) request('redirect_to', '');
        if ($targetUrl !== '' && str_starts_with($targetUrl, url('/'))) {
            return redirect()->to($targetUrl);
        }

        return back()->with('success', 'Notification marquée comme lue.');
    }
}

