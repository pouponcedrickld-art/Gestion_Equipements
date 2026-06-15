<?php

namespace App\DTO; // C'est l'adresse de la boîte où on range ce fichier (le dossier App/DTO).

use App\Models\Equipement; // On prépare l'outil pour pouvoir parler des vrais objets de l'application (les machines).
use App\Models\User; // On prépare l'outil pour pouvoir parler des utilisateurs (les humains).

class DeclarePanneData
{
    // Le constructeur, c'est la "recette" pour fabriquer cette fiche de signalement.
    // Pour remplir cette fiche, il faut obligatoirement donner les informations ci-dessous :
    public function __construct(
        public readonly int $equipementId,  
        // 👆 L'identifiant unique (le numéro de série) de la machine qui est en panne.
        
        public readonly int $agentId,       
        // 👆 Le numéro d'identification de la personne (l'agent) qui a vu la panne.
        
        public readonly string $description, 
        // 👆 Un texte où l'agent explique avec ses mots ce qui s'est passé (ex: "L'écran ne s'allume plus").
        
        public readonly string $niveauGravite, 
        // 👆 Pour dire si c'est grave ou pas (ex: "Bloquant", "Mineur").
        
        public readonly ?array $photos = null, 
        // 👆 Des photos du problème si on en a. Le "?" et le "= null" signifient que ce n'est pas obligatoire.
    ) {
        // C'est magique : en PHP moderne, le simple fait d'écrire les lignes du haut crée et remplit la fiche automatiquement !
    }
}