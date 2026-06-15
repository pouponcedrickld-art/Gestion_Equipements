<?php

namespace App\DTO; // L'adresse du dossier.

use Illuminate\Support\Arr; // Notre outil de fouille dans les tableaux.

class PanneDecisionDTO
{
    // La recette de la décision finale :
    public function __construct(
        public readonly int $panneId,          // Le numéro de la panne concernée.
        public readonly int $technicienId,     // Le numéro du technicien qui valide.
        public readonly string $decision,      // 📢 LA DÉCISION : Doit être obligatoirement "repaired" (réparé), "remplacement" ou "irrecuperable".
        public readonly ?float $coutEstimatif = null, // Le prix final validé (par défaut, rien du tout).
        public readonly ?string $dateDiagnostic = null, // La date.
        public readonly ?string $commentaires = null, // Les derniers mots ou justifications.
    ) {
    }

    /**
     * 🔄 CONNEXION WORKFLOW : Reçoit les données de l'API (le clic sur le bouton du site web) pour fabriquer la décision.
     */
    public static function fromArray(array $data): self
    {
        $decision = Arr::get($data, 'decision'); 
        // 👆 On attrape la décision écrite dans le formulaire (ex: "remplacement").

        // On fabrique l'objet Décision final :
        return new self(
            panneId: (int) Arr::get($data, 'panne_id'), // On récupère et transforme le numéro de panne.
            technicienId: (int) Arr::get($data, 'technicien_id'), // On récupère le numéro du technicien.
            decision: (string) $decision, // On valide le texte de la décision.
            
            coutEstimatif: Arr::has($data, 'cout_estime') ? (float) Arr::get($data, 'cout_estime') : null,
            // 👆 🪛 LIEN AVEC LE FICHIER PRÉCÉDENT : 
            // Dans le diagnostic on acceptait 'cout_estime' ou 'cout_reparation'. 
            // Ici, pour la décision, on ne cherche PLUS QUE 'cout_estime'. Si on ne le trouve pas, on met vide (null).
            
            dateDiagnostic: Arr::get($data, 'date_diagnostic'), // On prend la date.
            commentaires: Arr::get($data, 'commentaires') // On prend les commentaires.
        );
    }
}