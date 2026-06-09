<?php

namespace App\Notifications;

use App\Models\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlerteMaintenancePrevue extends Notification implements ShouldQueue
{
    use Queueable;

    public $maintenance;

    /**
     * Create a new notification instance.
     */
    public function __construct(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
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
        $equipement = $this->maintenance->equipement;
        $typeLibelle = $this->maintenance->type_maintenance === 'preventive' ? 'Préventive' : 'Corrective';
        $datePrevueFormatee = $this->maintenance->date_prevue->translatedFormat('l d F Y à H:i');
        
        return (new MailMessage)
            ->subject('🔧 Alerte : Maintenance prévue dans les prochaines heures')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("**Une maintenance {$typeLibelle} est planifiée** pour l'un de vos équipements.")
            ->line('')
            ->line('## 📋 Détails de la maintenance')
            ->line("**Équipement :** {$equipement->reference} ({$equipement->marque} {$equipement->modele})")
            ->line("**Date prévue :** {$datePrevueFormatee}")
            ->line("**Responsable :** {$this->maintenance->responsable}")
            ->line("**Type :** Maintenance {$typeLibelle}")
            ->line("**Durée estimée :** {$this->maintenance->duree_estimee}h")
            ->line('')
            ->action('📅 Consulter le calendrier de maintenance', url('/maintenances/calendrier'))
            ->line('')
            ->line('Merci de vous assurer que l\'équipement sera disponible à la date prévue.')
            ->salutation('Cordialement, L\'équipe de gestion des équipements');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'maintenance_id' => $this->maintenance->id,
            'equipement_reference' => $this->maintenance->equipement->reference,
            'date_prevue' => $this->maintenance->date_prevue,
            'responsable' => $this->maintenance->responsable,
            'type_maintenance' => $this->maintenance->type_maintenance,
            'message' => "Maintenance prévue pour {$this->maintenance->equipement->reference} le {$this->maintenance->date_prevue->format('d/m/Y')}",
        ];
    }
}
