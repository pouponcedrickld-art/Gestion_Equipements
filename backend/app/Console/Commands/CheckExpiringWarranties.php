<?php

namespace App\Console\Commands; // L'adresse où on range les robots textuels (les commandes de console).

use App\Services\GarantieAlertService; // 🔗 DÉPENDANCE CRUCIALE : Ce fichier ne sait pas calculer les dates tout seul. Il a besoin d'un assistant magicien qui s'appelle "GarantieAlertService".
use Illuminate\Console\Command; // Le moule de base de Laravel pour fabriquer des robots.
use Illuminate\Support\Facades\Log; // Un grand cahier secret où le robot écrit tout ce qu'il fait pour que les humains puissent vérifier s'il a bien travaillé.

class CheckExpiringWarranties extends Command
{
    /**
     * C'est le nom de code secret du robot. 
     * Si on écrit "php artisan notifications:check-expiring-warranties" dans le terminal, ce robot s'allume.
     */
    protected $signature = 'notifications:check-expiring-warranties';

    /**
     * C'est la petite phrase qui explique aux humains à quoi sert ce robot.
     */
    protected $description = 'Vérifier les garanties expirant et envoyer les alertes';

    /**
     * 🚀 Le bouton "DÉMARRER". C'est ici que tout se passe quand le robot s'allume.
     * * 🧠 EXPLICATION ENFANT DE 10 ANS : 
     * Le robot ouvre son sac et appelle son assistant `$alertService` (le GarantieAlertService).
     */
    public function handle(GarantieAlertService $alertService): void
    {
        // 1. Le robot écrit une phrase sur l'écran noir de la console pour dire qu'il commence.
        $this->info('Vérification des garanties expirant...');
        
        // 2. Il l'écrit aussi dans son cahier secret (le fichier Log).
        Log::info('Début de la commande notifications:check-expiring-warranties');

        try {
            // 🔥 LE GRAND TRAVAIL (Appel d'une fonction externe) :
            // Le robot dit à son assistant : "Hé, s'il te plaît, va fouiller dans la base de données, trouve les machines dont la garantie se termine bientôt, et envoie un e-mail ou une alerte aux chefs !".
            $alertService->verifierEtEnvoyerAlertes(); 
            // 👆 Si l'assistant réussit sans faire tomber d'assiettes...
            
            // 3. Le robot dit "Bravo, j'ai fini !" sur l'écran.
            $this->info('Vérification terminée avec succès !');
            // 4. Et il l'écrit joyeusement dans son cahier secret.
            Log::info('Fin de la commande notifications:check-expiring-warranties avec succès');
            
        } catch (\Exception $e) {
            // 🚨 LE PLAN DE SECOURS (Si ça explose ou s'il y a un bug) :
            // Si l'assistant trébuche (ex: la base de données est en panne)...
            
            // 3. Le robot affiche un message rouge "Erreur !" sur l'écran avec l'explication.
            $this->error('Erreur lors de la vérification: ' . $e->getMessage());
            
            // 4. Il écrit un gros SOS rouge dans son cahier secret avec toute l'histoire du bug (la trace) pour que les développeurs puissent réparer le robot.
            Log::error('Erreur lors de la vérification des garanties', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}