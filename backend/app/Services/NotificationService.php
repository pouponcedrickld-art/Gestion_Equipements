<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function createInApp(
        User $user,
        string $type,
        string $titre,
        string $message,
        array $data = [],
        string $canal = 'in_app',
        bool $lu = false
    ): Notification {
        return Notification::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'titre' => $titre,
            'message' => $message,
            'data' => $data,
            'canal' => $canal,
            'lu' => $lu,
        ]);
    }
}

