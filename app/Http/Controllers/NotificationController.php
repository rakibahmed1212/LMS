<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Notifications/Index', [
            'notifications' => $request->user()
                ->notifications()
                ->latest()
                ->paginate(15)
                ->through(fn (Notification $notification) => [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'channel' => $notification->channel,
                    'read_at' => $notification->read_at?->toDateTimeString(),
                    'created_at' => $notification->created_at?->diffForHumans(),
                ]),
        ]);
    }

    public function markRead(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['read_at' => now()]);

        return back();
    }
}
