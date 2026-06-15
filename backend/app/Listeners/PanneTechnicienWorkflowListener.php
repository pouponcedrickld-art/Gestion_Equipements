<?php

namespace App\Listeners;

use App\Events\PanneDeclaree;
use App\Events\PanneCloturee;
use App\Events\PanneDecisionIrrecuperable;
use App\Events\PanneDecisionReparee;
use App\Events\PanneDecisionRemplacementNecessaire;
use App\Events\PanneDiagnosticEnregistre;
use App\Models\User;
use App\Notifications\PanneClotureeNotification;
use App\Notifications\PanneDeclareeNotification;
use App\Notifications\PanneDecisionIrrecuperableNotification;
use App\Notifications\PanneDecisionRepareeNotification;
use App\Notifications\PanneDecisionRemplacementNotification;
use App\Notifications\PanneDiagnosticEnregistreNotification;
use Illuminate\Support\Facades\Notification;

class PanneTechnicienWorkflowListener
{
    private function sendNotifications(object $event, $notification): void
    {
        // Send to gestionnaire stock
        $gestionnaire = User::query()
            ->where('agence_id', $event->panne->equipement?->agence_actuelle_id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'gestionnaire_stock'))
            ->first();

        // Send to agent (if exists)
        $agent = $event->panne->agent?->user ?? null;

        $recipients = [];
        if ($gestionnaire) {
            $recipients[] = $gestionnaire;
        }
        if ($agent) {
            $recipients[] = $agent;
        }

        if (count($recipients) > 0) {
            Notification::send($recipients, $notification);
        }
    }

    public function handlePanneDeclaree(PanneDeclaree $event): void
    {
        $this->sendNotifications($event, new PanneDeclareeNotification(
            $event->panne,
            $event->actor
        ));
    }

    public function handlePanneDiagnosticEnregistre(PanneDiagnosticEnregistre $event): void
    {
        $this->sendNotifications($event, new PanneDiagnosticEnregistreNotification(
            $event->panne,
            $event->technicien
        ));
    }

    public function handlePanneDecisionReparee(PanneDecisionReparee $event): void
    {
        $this->sendNotifications($event, new PanneDecisionRepareeNotification(
            $event->panne,
            $event->technicien,
            $event->coutEstimatif,
            $event->commentaires
        ));
    }

    public function handlePanneDecisionRemplacementNecessaire(PanneDecisionRemplacementNecessaire $event): void
    {
        $this->sendNotifications($event, new PanneDecisionRemplacementNotification(
            $event->panne,
            $event->technicien,
            $event->coutEstimatif,
            $event->commentaires
        ));
    }

    public function handlePanneDecisionIrrecuperable(PanneDecisionIrrecuperable $event): void
    {
        $this->sendNotifications($event, new PanneDecisionIrrecuperableNotification(
            $event->panne,
            $event->technicien,
            $event->coutEstimatif,
            $event->commentaires
        ));
    }

    public function handlePanneCloturee(PanneCloturee $event): void
    {
        $this->sendNotifications($event, new PanneClotureeNotification(
            panne: $event->panne,
            acteur: $event->technicienOuActeur,
            coutEstimatif: $event->coutEstimatif,
            commentaires: $event->commentaires
        ));
    }
}
