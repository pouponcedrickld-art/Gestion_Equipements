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

    /**
     * Create a new notification instance.
     */
    public function __construct(Equipement $equipement)
    {
        $this->equipement = $equipement;
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
        return (new MailMessage)
            ->subject('Alerte : Garantie expirant dans 30 jours')
            ->greeting('Bonjour,')
            ->line("La garantie de l'équipement {$this->equipement->reference} expire dans 30 jours.")
            ->line("Date de fin de garantie : {$this->equipement->garantie_date_fin->format('d/m/Y')}")
            ->line("Équipement : {$this->equipement->marque} {$this->equipement->modele}")
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
            'message' => "Garantie expirant dans 30 jours pour l'équipement {$this->equipement->reference}",
        ];
    }
}
