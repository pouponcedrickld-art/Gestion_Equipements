<?php

namespace App\Notifications;

use App\Models\Equipement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlerteGarantieExpireNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $equipement;
    public $seuilJours;

    /**
     * Create a new notification instance.
     */
    public function __construct(Equipement $equipement, int $seuilJours = 30)
    {
        $this->equipement = $equipement;
        $this->seuilJours = $seuilJours;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = "Alerte : Garantie expirant dans {$this->seuilJours} jours";
        
        return (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour,')
            ->line("La garantie de l'équipement {$this->equipement->reference} expire dans {$this->seuilJours} jours.")
            ->line("Date de fin de garantie : {$this->equipement->garantie_date_fin->format('d/m/Y')}")
            ->line("Équipement : {$this->equipement->marque} {$this->equipement->modele}")
            ->line("Référence : {$this->equipement->reference}")
            ->action('Voir l\'équipement', url('/equipements/' . $this->equipement->id))
            ->line('Merci de prendre les dispositions nécessaires.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'equipement_id' => $this->equipement->id,
            'equipement_reference' => $this->equipement->reference,
            'garantie_date_fin' => $this->equipement->garantie_date_fin,
            'marque' => $this->equipement->marque,
            'modele' => $this->equipement->modele,
            'seuil_jours' => $this->seuilJours,
            'message' => "Garantie expirant dans {$this->seuilJours} jours pour l'équipement {$this->equipement->reference}",
        ];
    }
}
