<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('notifications.view_all') || $user->hasPermissionTo('notifications.view_own');
    }

    public function view(User $user, Notification $notification): bool
    {
        if ($user->hasPermissionTo('notifications.view_all')) return true;
        return $user->hasPermissionTo('notifications.view_own') && $user->id === $notification->user_id;
    }

    public function envoyer(User $user): bool
    {
        return $user->hasPermissionTo('notifications.envoyer');
    }

    public function configurer(User $user): bool
    {
        return $user->hasPermissionTo('notifications.configurer');
    }
}
