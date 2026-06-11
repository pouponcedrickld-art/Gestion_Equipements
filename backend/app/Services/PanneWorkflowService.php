<?php

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
 * - Centraliser les transitions autorisées
 * - Mettre à jour Panne + Equipement
 * - Historiser chaque changement de statut
 * - Déclencher les événements système
 */
class PanneWorkflowService
{
    /**
     * Statuts normalisés (compatibles avec la migration pannes).
     */
    public const STATUT_DECLAREE = 'declaree';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_EN_MAINTENANCE = 'en_maintenance';
    public const STATUT_RESOLUE = 'resolue';
    public const STATUT_IRRECUPERABLE = 'irrecuperable';

    /**
     * Transitions autorisées.
     */
    private const TRANSITIONS = [
        self::STATUT_DECLAREE => [
            self::STATUT_EN_COURS,
            self::STATUT_EN_MAINTENANCE,
            self::STATUT_IRRECUPERABLE,
        ],
        self::STATUT_EN_COURS => [
            self::STATUT_EN_MAINTENANCE,
            self::STATUT_RESOLUE,
            self::STATUT_IRRECUPERABLE,
        ],
        self::STATUT_EN_MAINTENANCE => [
            self::STATUT_RESOLUE,
            self::STATUT_IRRECUPERABLE,
        ],
        self::STATUT_RESOLUE => [],
        self::STATUT_IRRECUPERABLE => [],
    ];

    /**
     * Déclarer une panne.
     *
     * - Met à jour le statut vers declaree (par défaut)
     * - Met à jour l'équipement vers en_panne
     * - Historise la transition
     * - Déclenche PanneDeclaree
     */
    public function declarer(Panne $panne, User $actor, array $payload = []): Panne
    {
        return DB::transaction(function () use ($panne, $actor, $payload) {
            $equipement = $panne->equipement()->lockForUpdate()->firstOrFail();

            // Mise à jour équipement
            $this->applyEquipementEtat($equipement, 'en_panne');

            $statutAvant = $panne->statut;
            $panne->fill([
                'statut' => self::STATUT_DECLAREE,
                'date_declaration' => $panne->date_declaration ?? now(),
            ])->save();

            $this->createHistory($panne, $actor, $statutAvant, self::STATUT_DECLAREE, $payload);

            // Event + notification (si listeners existent)
            event(new PanneDeclaree($panne, $actor));

            return $panne;
        });
    }

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
     * Transition générique.
     */
    private function transition(Panne $panne, User $actor, string $nouveauStatut, array $payload): Panne
    {
        return DB::transaction(function () use ($panne, $actor, $nouveauStatut, $payload) {
            $panne = Panne::query()->whereKey($panne->id)->lockForUpdate()->firstOrFail();
            $statutAvant = $panne->statut;

            $this->assertTransitionAutorisee($statutAvant, $nouveauStatut);

            // Mapping vers statut équipemement (statut_global)
            $equipement = $panne->equipement()->lockForUpdate()->firstOrFail();
            $this->applyEquipementEtatPourPanne($equipement, $nouveauStatut);

            // Mise à jour panne (champs optionnels payload)
            $update = [
                'statut' => $nouveauStatut,
                // Résolution
                'date_resolution' => $nouveauStatut === self::STATUT_RESOLUE ? ($panne->date_resolution ?? now()) : $panne->date_resolution,
            ];

            // Champs optionnels métier (utilisés aussi dans l'historique)
            $update = array_merge($update, Arr::only($payload, [
                'diagnostic_technicien',
                'action_realisee',
                'cout_reparation',
                'decision_finale',
                'solution',
            ]));

            $panne->fill($update);
            $panne->save();

            $this->createHistory($panne, $actor, $statutAvant, $nouveauStatut, $payload);

            // TODO: événements spécifiques par transition (si besoin)
            // Pour l’instant, on centralise l’historique.

            return $panne;
        });
    }

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

    /**
     * Met à jour l’équipement (statut_global/etat) à partir du statut de panne.
     */
    private function applyEquipementEtatPourPanne(Equipement $equipement, string $statutPanne): void
    {
        // Ici on respecte la logique existante du projet :
        // - la panne force au minimum en_panne
        // - en_maintenance force en_maintenance
        // - résolue / irrécupérable sort de l’état panne

        $mapping = match ($statutPanne) {
            self::STATUT_DECLAREE => ['statut_global' => 'en_panne'],
            self::STATUT_EN_COURS => ['statut_global' => 'en_panne'],
            self::STATUT_EN_MAINTENANCE => ['statut_global' => 'en_maintenance'],
            self::STATUT_RESOLUE => ['statut_global' => 'en_stock_local', 'etat' => 'en_service'],
            self::STATUT_IRRECUPERABLE => ['statut_global' => 'reforme', 'etat' => 'hors_service'],
            default => null,
        };

        if (!$mapping) {
            return;
        }

        // Utilise les helpers de tracabilité existants si le projet les utilise.
        // Par sécurité on fait simple: update.
        $equipement->update($mapping);

        // Si votre Equipement::createMouvement supporte les valeurs,
        // on peut aussi tracer ici, mais ce refactor est volontairement minimal.
    }

    private function applyEquipementEtat(Equipement $equipement, string $statutGlobal): void
    {
        $equipement->update(['statut_global' => $statutGlobal]);
    }

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

