<?php

namespace App\Notifications;

use App\Models\DemandeMateriel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandeMaterielTraiteeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public DemandeMateriel $demande;
    public string $decision;

    public function __construct(DemandeMateriel $demande, string $decision)
    {
        $this->demande = $demande;
        $this->decision = $decision;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statut = $this->decision === 'Approuver' ? 'approuvée' : 'refusée';
        return (new MailMessage)
            ->subject("Demande de matériel {$statut}")
            ->line("Votre demande de {$this->demande->equipement?->nom} (x{$this->demande->quantite}) a été {$statut}.")
            ->action('Voir la demande', url("/demandes-materiel/{$this->demande->id}"))
            ->line($this->demande->observations ? "Motif : {$this->demande->observations}" : '');
    }

    public function toArray(object $notifiable): array
    {
        $statut = $this->decision === 'Approuver' ? 'approuvée' : 'refusée';
        return [
            'type' => 'demande_traitee',
            'demande_id' => $this->demande->id,
            'decision' => $this->decision,
            'message' => "Demande #{$this->demande->id} {$statut} : {$this->demande->equipement?->nom} x{$this->demande->quantite}",
        ];
    }
}
