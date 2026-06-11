<?php

namespace App\Listeners;

use App\Events\PanneDecisionIrrecuperable;
use App\Events\PanneDecisionReparee;
use App\Events\PanneDecisionRemplacementNecessaire;
use App\Events\PanneDiagnosticEnregistre;
use App\Models\User;
use App\Notifications\PanneDecisionRepareeNotification;
use App\Notifications\PanneDecisionRemplacementNotification;
use App\Notifications\PanneDecisionIrrecuperableNotification;
use App\Notifications\PanneDiagnosticEnregistreNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;

class PanneTechnicienWorkflowListener
{
    public function handlePanneDiagnosticEnregistre(PanneDiagnosticEnregistre $event): void
    {
        $panne = $event->panne;
        $technicien = $event->technicien;

        // Gestionnaire stock : via policy/roles existants
        $gestionnaire = User::query()
            ->where('agence_id', $technicien->agence_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'gestionnaire_stock'))
            ->first();

        Notification::send($gestionnaire ? [$gestionnaire] : [], new PanneDiagnosticEnregistreNotification($panne, $technicien));
    }

    public function handlePanneDecisionReparee(PanneDecisionReparee $event): void
    {
        $panne = $event->panne;
        $technicien = $event->technicien;

        $gestionnaire = User::query()
            ->where('agence_id', $technicien->agence_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'gestionnaire_stock'))
            ->first();

        Notification::send($gestionnaire ? [$gestionnaire] : [], new PanneDecisionRepareeNotification($panne, $technicien, $event->coutEstimatif, $event->commentaires));
    }

    public function handlePanneDecisionRemplacementNecessaire(PanneDecisionRemplacementNecessaire $event): void
    {
        $panne = $event->panne;
        $technicien = $event->technicien;

        $gestionnaire = User::query()
            ->where('agence_id', $technicien->agence_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'gestionnaire_stock'))
            ->first();

        Notification::send($gestionnaire ? [$gestionnaire] : [], new PanneDecisionRemplacementNotification($panne, $technicien, $event->coutEstimatif, $event->commentaires));
    }

    public function handlePanneDecisionIrrecuperable(PanneDecisionIrrecuperable $event): void
    {
        $panne = $event->panne;
        $technicien = $event->technicien;

        $gestionnaire = User::query()
            ->where('agence_id', $technicien->agence_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'gestionnaire_stock'))
            ->first();

        Notification::send($gestionnaire ? [$gestionnaire] : [], new PanneDecisionIrrecuperableNotification($panne, $technicien, $event->coutEstimatif, $event->commentaires));
    }
}

