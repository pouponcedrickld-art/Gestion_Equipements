<?php

namespace App\Notifications;

use App\Models\Panne;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PanneDecisionRepareeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Panne $panne,
        public readonly User $technicien,
        public readonly ?float $coutEstimatif = null,
        public readonly ?string $commentaires = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Décision: panne réparée.')
            ->line("Panne ID: {$this->panne->id}")
            ->line("Technicien: {$this->technicien->name ?? $this->technicien->id}")
            ->when($this->coutEstimatif !== null, fn (MailMessage $m) => $m->line('Coût estimatif: '.$this->coutEstimatif))
            ->when($this->commentaires, fn (MailMessage $m) => $m->line('Commentaires: '.$this->commentaires));
    }
}

