<?php

namespace App\Notifications;

use App\Models\Panne;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PanneDeclareeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Panne $panne,
        public readonly User $actor,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle panne déclarée')
            ->greeting('Bonjour,')
            ->line('Une nouvelle panne a été déclarée.')
            ->line("Équipement: {$this->panne->equipement?->nom}")
            ->line("Description: {$this->panne->description}")
            ->line("Gravité: {$this->panne->niveau_gravite}")
            ->line("Déclarée par: {$this->actor->name}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'panne_id' => $this->panne->id,
            'equipement' => $this->panne->equipement?->nom,
            'description' => $this->panne->description,
        ];
    }
}
