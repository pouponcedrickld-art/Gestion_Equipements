<?php

namespace App\Notifications;

use App\Models\Transfert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransfertApprouveNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Transfert $transfert;

    public function __construct(Transfert $transfert)
    {
        $this->transfert = $transfert;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Transfert #{$this->transfert->id} approuvé")
            ->line("Le transfert de {$this->transfert->equipement?->nom} vers {$this->transfert->agenceDestination?->nom} a été approuvé.")
            ->action('Voir le transfert', url("/transferts/{$this->transfert->id}"))
            ->line("Vous pouvez maintenant procéder à l'expédition.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'transfert_approuve',
            'transfert_id' => $this->transfert->id,
            'message' => "Transfert #{$this->transfert->id} approuvé : {$this->transfert->equipement?->nom} → {$this->transfert->agenceDestination?->nom}",
            'equipement' => $this->transfert->equipement?->nom,
            'destination' => $this->transfert->agenceDestination?->nom,
        ];
    }
}
