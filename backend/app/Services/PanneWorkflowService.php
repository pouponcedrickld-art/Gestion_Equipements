<?php

// =============================================
// FICHIER : PanneWorkflowService.php
// RÔLE : Service qui gère TOUT le workflow (cycle de vie) des pannes
// Il s'assure que chaque transition de statut est autorisée, et met à jour à la fois la panne ET l'équipement
// Il garde aussi l'historique de chaque changement !
// =============================================

namespace App\Services;

use App\Events\PanneDeclaree;

use App\Models\Equipement;
use App\Models\Panne;
use App\Models\PanneStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Service de workflow métier pour la gestion des pannes.
 *
 * Objectifs :
 * - Centraliser les transitions autorisées (on ne peut pas passer n'importe où !)
 * - Mettre à jour Panne + Equipement en même temps (transaction DB)
 * - Historiser chaque changement de statut (pour savoir qui a fait quoi)
 * - Déclencher les événements système (notifications, etc.)
 */
class PanneWorkflowService
{
    // =============================================
    // LES STATUTS POSSIBLES POUR UNE PANNE
    // =============================================
    public const STATUT_DECLAREE = 'declaree';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_EN_MAINTENANCE = 'en_maintenance';
    public const STATUT_RESOLUE = 'resolue';
    public const STATUT_IRRECUPERABLE = 'irrecuperable';

    // =============================================
    // LES TRANSITIONS AUTORISÉES (d'un statut à un autre)
    // =============================================
    // Exemple : depuis "déclarée", on peut aller vers "en cours", "en maintenance", etc.
    private const TRANSITIONS = [
        self::STATUT_DECLAREE => [
            self::STATUT_EN_COURS,
            self::STATUT_EN_MAINTENANCE,
            self::STATUT_IRRECUPERABLE,
            'cloturee',
        ],
        self::STATUT_EN_COURS => [
            self::STATUT_EN_MAINTENANCE,
            self::STATUT_RESOLUE,
            self::STATUT_IRRECUPERABLE,
            'cloturee',
        ],
        self::STATUT_EN_MAINTENANCE => [
            self::STATUT_RESOLUE,
            self::STATUT_IRRECUPERABLE,
            'cloturee',
        ],
        self::STATUT_RESOLUE => [], // pas de transition depuis résolu
        self::STATUT_IRRECUPERABLE => [], // pas de transition depuis irrécupérable
    ];


    // =============================================
    // MÉTHODE : DÉCLARER UNE PANNE
    // =============================================
    /**
     * Déclarer une panne.
     *
     * - Met à jour le statut vers declaree (par défaut)
     * - Met à jour l'équipement vers en_panne
     * - Historise la transition
     * - Déclenche PanneDeclaree (événement)
     */
    public function declarer(Panne $panne, User $actor, array $payload = []): Panne
    {
        // On utilise une transaction DB : si quelque chose casse, on annule TOUT
        return DB::transaction(function () use ($panne, $actor, $payload) {
            // On verrouille l'équipement pour qu'il ne soit pas modifié en même temps par quelqu'un d'autre
            $equipement = $panne->equipement()->lockForUpdate()->firstOrFail();

            // Mise à jour de l'équipement : il est maintenant en panne
            $this->applyEquipementEtat($equipement, 'en_panne');

            $statutAvant = $panne->statut;
            $panne->fill([
                'statut' => self::STATUT_DECLAREE,
                'date_declaration' => $panne->date_declaration ?? now(),
            ])->save();

            // On ajoute une ligne à l'historique
            $this->createHistory($panne, $actor, $statutAvant, self::STATUT_DECLAREE, $payload);

            // On déclenche l'événement "Panne déclarée" (pour les notifications, etc.)
            event(new PanneDeclaree($panne, $actor));

            // On envoie une notification aux personnes concernées
            $recipients = User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock', 'technicien_maintenance']))
                ->where(fn($q) => $q->where('agence_id', $equipement->agence_actuelle_id)->orWhereHas('roles', fn($rq) => $rq->whereIn('name', ['super_admin', 'gestionnaire_stock_general'])))
                ->get();

            foreach ($recipients as $recipient) {
                \App\Services\NotificationService::sendNotification(
                    user: $recipient,
                    type: 'panne_declaree',
                    title: 'Nouvelle panne déclarée',
                    message: "Une nouvelle panne a été déclarée pour l'équipement {$equipement->nom} ({$equipement->reference}).",
                    data: ['panne_id' => $panne->id, 'equipement_id' => $equipement->id],
                    channels: ['in_app', 'email']
                );
            }

            return $panne;
        });
    }

    // =============================================
    // MÉTHODES RACCOURCIS POUR LES TRANSITIONS
    // =============================================

    /**
     * Transmettre la panne (agent/stock -> technicien).
     */
    public function passerEnCours(Panne $panne, User $actor, array $payload = []): Panne
    {
        return $this->transition($panne, $actor, self::STATUT_EN_COURS, $payload);
    }

    /**
     * Passer en maintenance.
     */
    public function passerEnMaintenance(Panne $panne, User $actor, array $payload = []): Panne
    {
        return $this->transition($panne, $actor, self::STATUT_EN_MAINTENANCE, $payload);
    }

    /**
     * Résoudre la panne.
     */
    public function resoudre(Panne $panne, User $actor, array $payload = []): Panne
    {
        return $this->transition($panne, $actor, self::STATUT_RESOLUE, $payload);
    }

    /**
     * Marquer irrécupérable.
     */
    public function marquerIrrecuperable(Panne $panne, User $actor, array $payload = []): Panne
    {
        return $this->transition($panne, $actor, self::STATUT_IRRECUPERABLE, $payload);
    }

    /**
     * Clôturer une panne (statut final).
     * Cette transition est “corrective” : historisation + mise à jour équipement.
     */
    public function cloturer(Panne $panne, User $actor, array $payload = []): Panne
    {
        // On force un statut final : resolue ou irrecuperable ne couvre pas la clôture administrative.
        // Convention: cloturee.
        $panne = $this->transition($panne, $actor, 'cloturee', $payload);

        // On envoie une notification pour dire que la panne est résolue
        $equipement = $panne->equipement()->firstOrFail();
        $recipients = User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'gestionnaire_stock_general', 'chef_agence', 'gestionnaire_stock']))
            ->where(fn($q) => $q->where('agence_id', $equipement->agence_actuelle_id)->orWhereHas('roles', fn($rq) => $rq->whereIn('name', ['super_admin', 'gestionnaire_stock_general'])))
            ->get();

        foreach ($recipients as $recipient) {
            \App\Services\NotificationService::sendNotification(
                user: $recipient,
                type: 'panne_resolue',
                title: 'Panne résolue',
                message: "La panne sur l'équipement {$equipement->nom} ({$equipement->reference}) a été résolue.",
                data: ['panne_id' => $panne->id, 'equipement_id' => $equipement->id],
                channels: ['in_app', 'email']
            );
        }

        return $panne;
    }


    // =============================================
    // MÉTHODE GÉNÉRIQUE : FAIRE UNE TRANSITION
    // =============================================
    /**
     * Transition générique.
     */
    private function transition(Panne $panne, User $actor, string $nouveauStatut, array $payload): Panne
    {
        return DB::transaction(function () use ($panne, $actor, $nouveauStatut, $payload) {
            // On verrouille la panne
            $panne = Panne::query()->whereKey($panne->id)->lockForUpdate()->firstOrFail();
            $statutAvant = $panne->statut;

            // On vérifie que la transition est autorisée (on ne peut pas passer n'importe où !)
            $this->assertTransitionAutorisee($statutAvant, $nouveauStatut);

            // Mapping vers statut équipemement (statut_global)
            $equipement = $panne->equipement()->lockForUpdate()->firstOrFail();
            $this->applyEquipementEtatPourPanne($equipement, $nouveauStatut);

            // Mise à jour de la panne
            $update = [
                'statut' => $nouveauStatut,
                // Si c'est résolu, on met la date de résolution
                'date_resolution' => $nouveauStatut === self::STATUT_RESOLUE ? ($panne->date_resolution ?? now()) : $panne->date_resolution,
            ];

            // On ajoute les champs optionnels (diagnostic, coût, etc.)
            $update = array_merge($update, Arr::only($payload, [
                'diagnostic_technicien',
                'action_realisee',
                'cout_reparation',
                'decision_finale',
                'solution',
            ]));

            $panne->fill($update);
            $panne->save();

            // On ajoute l'historique
            $this->createHistory($panne, $actor, $statutAvant, $nouveauStatut, $payload);

            return $panne;
        });
    }

    // =============================================
    // MÉTHODE : VÉRIFIER SI LA TRANSITION EST AUTORISÉE
    // =============================================
    /**
     * Vérifie qu’une transition est autorisée.
     */
    private function assertTransitionAutorisee(string $statutAvant, string $nouveauStatut): void
    {
        $liste = self::TRANSITIONS[$statutAvant] ?? [];
        if (!in_array($nouveauStatut, $liste, true)) {
            throw new RuntimeException("Transition de panne non autorisée : {$statutAvant} → {$nouveauStatut}");
        }
    }

    // =============================================
    // MÉTHODE : METTRE À JOUR L'ÉQUIPEMENT SELON LE STATUT DE LA PANNE
    // =============================================
    /**
     * Met à jour l’équipement (statut_global/etat) à partir du statut de panne.
     */
    private function applyEquipementEtatPourPanne(Equipement $equipement, string $statutPanne): void
    {
        // On définit le mapping : selon le statut de la panne, quel est le statut de l'équipement ?
        $mapping = match ($statutPanne) {
            self::STATUT_DECLAREE => ['statut_global' => 'en_panne'],
            self::STATUT_EN_COURS => ['statut_global' => 'en_panne'],
            self::STATUT_EN_MAINTENANCE => ['statut_global' => 'en_maintenance'],
            self::STATUT_RESOLUE => ['statut_global' => 'en_stock_local', 'etat' => 'en_service'],
            self::STATUT_IRRECUPERABLE => ['statut_global' => 'reforme', 'etat' => 'hors_service'],
            'cloturee' => ['statut_global' => 'en_stock_local', 'etat' => 'en_service'],
            default => null,
        };

        if (!$mapping) {
            return;
        }

        $equipement->update($mapping);
    }

    // =============================================
    // MÉTHODE AUXILIAIRE : METTRE À JOUR LE STATUT DE L'ÉQUIPEMENT
    // =============================================
    private function applyEquipementEtat(Equipement $equipement, string $statutGlobal): void
    {
        $equipement->update(['statut_global' => $statutGlobal]);
    }

    // =============================================
    // MÉTHODE : CRÉER UNE LIGNE D'HISTORIQUE
    // =============================================
    /**
     * Crée une ligne d’historique.
     */
    private function createHistory(Panne $panne, User $actor, ?string $statutAncien, string $statutNouveau, array $payload): void
    {
        PanneStatusHistory::create([
            'panne_id' => $panne->id,
            'statut_ancien' => $statutAncien,
            'statut_nouveau' => $statutNouveau,
            'commentaire' => $payload['commentaire'] ?? null,
            'action_realisee' => $payload['action_realisee'] ?? null,
            'cout_reparation' => $payload['cout_reparation'] ?? null,
            'created_by' => $actor->id,
        ]);
    }
}
