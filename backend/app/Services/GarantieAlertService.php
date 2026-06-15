<?php

namespace App\Services;

use App\Models\Equipement;
use App\Models\GarantieAlerte;
use App\Models\User;
use App\Notifications\AlerteGarantieExpireNotification;
use Illuminate\Support\Facades\Log;

class GarantieAlertService
{
    public const SEUILS_JOURS = [90, 60, 30, 7];

    /**
     * Vérifie et envoie les alertes de garantie
     */
    public function verifierEtEnvoyerAlertes(): void
    {
        Log::info('Début de la vérification des alertes de garantie');

        $seuilsTraites = [];

        foreach (self::SEUILS_JOURS as $seuil) {
            $equipements = $this->getEquipementsPourSeuil($seuil);

            foreach ($equipements as $equipement) {
                if ($this->alerteDejaEnvoyee($equipement, $seuil)) {
                    continue;
                }

                $this->envoyerAlertePourEquipement($equipement, $seuil);
                $this->enregistrerAlerteEnvoyee($equipement, $seuil);
                $seuilsTraites[$seuil][] = $equipement->id;
            }
        }

        Log::info('Fin de la vérification des alertes de garantie', [
            'seuils_traites' => $seuilsTraites
        ]);
    }

    /**
     * Récupère les équipements pour un seuil de jours donné
     */
    private function getEquipementsPourSeuil(int $seuilJours): \Illuminate\Database\Eloquent\Collection
    {
        $dateLimite = now()->addDays($seuilJours);
        $dateLimitePrecedent = now()->addDays($seuilJours + 1);

        return Equipement::whereNotNull('garantie_date_fin')
            ->where('garantie_date_fin', '<=', $dateLimite)
            ->where('garantie_date_fin', '>=', $dateLimitePrecedent->subDay())
            ->with('agenceActuelle')
            ->get();
    }

    /**
     * Vérifie si l'alerte a déjà été envoyée pour cet équipement et ce seuil
     */
    private function alerteDejaEnvoyee(Equipement $equipement, int $seuilJours): bool
    {
        return GarantieAlerte::where('equipement_id', $equipement->id)
            ->where('seuil_jours', $seuilJours)
            ->exists();
    }

    /**
     * Enregistre que l'alerte a été envoyée
     */
    private function enregistrerAlerteEnvoyee(Equipement $equipement, int $seuilJours): void
    {
        GarantieAlerte::create([
            'equipement_id' => $equipement->id,
            'seuil_jours' => $seuilJours,
            'date_envoi' => now(),
        ]);
    }

    /**
     * Envoie l'alerte pour un équipement
     */
    private function envoyerAlertePourEquipement(Equipement $equipement, int $seuilJours): void
    {
        $users = $this->getUsersANotifier($equipement);

        foreach ($users as $user) {
            $user->notify(new AlerteGarantieExpireNotification($equipement, $seuilJours));
        }
    }

    /**
     * Récupère les utilisateurs à notifier
     */
    private function getUsersANotifier(Equipement $equipement): \Illuminate\Database\Eloquent\Collection
    {
        // Utilisateurs super admin, gestionnaire stock général
        $query = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['super_admin', 'gestionnaire_stock_general']);
        });

        // Plus chef d'agence et gestionnaire stock local pour l'agence concernée
        if ($equipement->agence_actuelle_id) {
            $query->orWhere(function ($q) use ($equipement) {
                $q->where('agence_id', $equipement->agence_actuelle_id)
                    ->whereHas('roles', function ($rq) {
                        $rq->whereIn('name', ['chef_agence', 'gestionnaire_stock']);
                    });
            });
        }

        return $query->get();
    }
}
