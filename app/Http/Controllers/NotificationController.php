<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $notifications = $user->notifications()->orderByDesc('created_at')->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($request->user()->id === $notification->notifiable_id && get_class($request->user()) === $notification->notifiable_type, 403);
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }
        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('status', __('notifications.all_marked_read'));
    }
}

