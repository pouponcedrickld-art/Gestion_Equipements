<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public static function sendNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        array $channels = ['in_app']
    ): void
    {
        // Send in-app notification
        if (in_array('in_app', $channels)) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'titre' => $title,
                'message' => $message,
                'data' => $data,
                'lu' => false,
                'canal' => 'in_app'
            ]);
        }

        // Send email notification
        if (in_array('email', $channels)) {
            self::sendEmailNotification($user, $title, $message);
        }
    }

    protected static function sendEmailNotification(User $user, string $title, string $message): void
    {
        Mail::send([], [], function ($mail) use ($user, $title, $message) {
            $mail->to($user->email)
                 ->subject($title)
                 ->html("<p>{$message}</p>");
        });
    }

    public static function getNotificationTypes(): array
    {
        return [
            'panne_declaree' => 'Panne déclarée',
            'panne_resolue' => 'Panne résolue',
            'maintenance_programmee' => 'Maintenance programmée',
            'maintenance_terminee' => 'Maintenance terminée',
            'transfert_valide' => 'Transfert validé',
            'transfert_recu' => 'Transfert reçu',
            'retour_en_retard' => 'Retour en retard',
            'garantie_proche_expiration' => 'Garantie proche expiration'
        ];
    }
}
