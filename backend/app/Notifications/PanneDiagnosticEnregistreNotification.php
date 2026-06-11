<?php

namespace App\Notifications;

use App\Models\Panne;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PanneDiagnosticEnregistreNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Panne $panne,
        public readonly User $technicien
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Diagnostic enregistré pour une panne.')
            ->line("Panne ID: {$this->panne->id}")
            ->line("Technicien: {$this->technicien->name ?? $this->technicien->id}");
    }
}

