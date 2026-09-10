<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function notify(User $user, string $type, string $title, ?string $body = null, array $data = []): Notification
    {
        return Notification::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'channel' => 'in_app',
            'data' => $data,
        ]);
    }
}
