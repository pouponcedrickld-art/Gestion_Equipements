<?php

namespace App\DTO; // L'adresse du dossier.

use Illuminate\Support\Arr; // Un outil super pratique de Laravel pour fouiller et attraper des données dans un tableau.
use Illuminate\Support\Str; // Un outil pour manipuler du texte (même si on ne l'utilise pas directement ici).
use InvalidArgumentException; // Une alerte de sécurité si quelqu'un écrit une bêtise.

class PanneDiagnosticDTO
{
    // C'est la "recette" pour fabriquer le rapport de l'examen du technicien.
    public function __construct(
        public readonly int $panneId,         // Le numéro de la panne (qui vient de la fiche de signalement précédente).
        public readonly int $technicienId,    // Le numéro d'identification du réparateur.
        public readonly string $diagnostic,   // Ce que le technicien a découvert (ex: "Le câble bleu est grillé").
        public readonly ?float $coutEstime,   // Le prix que ça va coûter selon lui (ex: 150.50). Peut être vide.
        public readonly ?string $dateDiagnostic, // Le jour et l'heure où il a fait l'examen.
        public readonly ?string $commentaires,   // Des notes bonus s'il a des choses à ajouter.
        public readonly ?string $decision,     // Ce qu'il propose de faire (ex: "À réparer").
    ) {
        // 🛑 ZONE DE SÉCURITÉ (Validation)
        // On vérifie si le technicien n'a pas fait une erreur de saisie.
        if ($coutEstime !== null && $coutEstime < 0) {
            // Si le prix estimé existe mais qu'il est plus petit que 0 (un prix négatif, ça n'existe pas !)...
            throw new InvalidArgumentException('coutEstime doit être >= 0');
            // ...alors on arrête tout et on crie : "Hé ! Le prix ne peut pas être négatif !".
        }
    }

    /**
     * 🔄 CONNEXION WORKFLOW : Cette fonction transforme les données brutes envoyées par l'application ou un formulaire en une vraie fiche propre.
     */
    public static function fromArray(array $data): self
    {
        // On crée et on renvoie une nouvelle fiche de diagnostic en rangeant chaque information au bon endroit :
        return new self(
            panneId: (int) Arr::get($data, 'panne_id'), 
            // 👆 On cherche 'panne_id' dans le formulaire et on force sa transformation en nombre entier (int).
            
            technicienId: (int) Arr::get($data, 'technicien_id'), 
            // 👆 Pareil pour le numéro du technicien.
            
            diagnostic: (string) Arr::get($data, 'diagnostic_technicien'), 
            // 👆 On prend le texte du diagnostic. Note que dans le formulaire ça s'appelait 'diagnostic_technicien', mais on le range dans 'diagnostic'.
            
            coutEstime: Arr::has($data, 'cout_estime') ? (float) Arr::get($data, 'cout_estime') : (Arr::has($data, 'cout_reparation') ? (float) Arr::get($data, 'cout_reparation') : null),
            // 👆 🧠 EXPLICATION TERRE À TERRE : 
            // On joue aux devinettes : "Est-ce qu'on a écrit 'cout_estime' ?" 
            // Si oui, on prend ce montant. 
            // Si non, on demande : "Est-ce qu'on a écrit 'cout_reparation' à la place ?" 
            // Si oui, on prend ce montant. Si on n'a aucun des deux, on met "null" (rien du tout).
            
            dateDiagnostic: Arr::get($data, 'date_diagnostic'), 
            // 👆 On récupère la date de l'examen si elle y est.
            
            commentaires: Arr::get($data, 'commentaires'), 
            // 👆 On prend les remarques.
            
            decision: Arr::get($data, 'decision'), 
            // 👆 On prend l'avis du technicien.
        );
    }
}