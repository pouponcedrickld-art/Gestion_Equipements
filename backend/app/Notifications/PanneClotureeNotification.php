<?php

namespace App\Notifications;

use App\Models\Panne;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PanneClotureeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Panne $panne,
        public readonly User $acteur,
        public readonly ?float $coutEstimatif = null,
        public readonly ?string $commentaires = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->line('La panne a été clôturée.')
            ->line('Équipement: ' . ($this->panne->equipement?->reference ?? $this->panne->equipement_id))
            ->line('Acteur: ' . ($this->acteur->name ?? $this->acteur->id))
            ->when($this->coutEstimatif !== null, fn (MailMessage $m) => $m->line('Coût: ' . $this->coutEstimatif))
            ->when($this->commentaires, fn (MailMessage $m) => $m->line('Commentaires: ' . $this->commentaires));
    }
}

