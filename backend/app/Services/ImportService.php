<?php

namespace App\Services;

use App\Models\Equipement;
use App\Models\Categorie;
use App\Models\Agence;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportService
{
    protected $erreurs = [];
    protected $succes = [];
    protected $lignesTraitees = 0;

    /**
     * Importer des équipements depuis un fichier CSV/Excel
     * 
     * @param UploadedFile $file
     * @param int $userId
     * @param int|null $agenceId
     * @return array
     */
    public function importEquipements(UploadedFile $file, int $userId, ?int $agenceId = null): array
    {
        $this->resetCounters();

        // Déterminer l'agence de destination
        $agence = $agenceId ? Agence::find($agenceId) : Agence::where('type', 'generale')->first();
        
        if (!$agence) {
            throw new \Exception('Agence de destination non trouvée');
        }

        // Lire le fichier selon le type
        $donnees = $this->lireFichier($file);
        
        if (empty($donnees)) {
            throw new \Exception('Le fichier est vide ou illisible');
        }

        // Valider et traiter chaque ligne
        DB::beginTransaction();
        
        try {
            foreach ($donnees as $numeroLigne => $ligne) {
                $this->lignesTraitees++;
                $this->traiterLigneEquipement($ligne, $numeroLigne + 2, $userId, $agence); // +2 car ligne 1 = headers
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Import terminé avec succès",
                'statistiques' => [
                    'lignes_traitees' => $this->lignesTraitees,
                    'succes' => count($this->succes),
                    'erreurs' => count($this->erreurs),
                ],
                'details' => [
                    'succes' => $this->succes,
                    'erreurs' => $this->erreurs,
                ]
            ];

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Prévisualiser l'import sans sauvegarder
     * 
     * @param UploadedFile $file
     * @param int $maxLignes
     * @return array
     */
    public function previewImport(UploadedFile $file, int $maxLignes = 10): array
    {
        $donnees = $this->lireFichier($file);
        
        if (empty($donnees)) {
            throw new \Exception('Le fichier est vide ou illisible');
        }

        $preview = [];
        $erreurs = [];
        
        // Analyser seulement les premières lignes
        $lignesAAnalyser = array_slice($donnees, 0, $maxLignes);
        
        foreach ($lignesAAnalyser as $numeroLigne => $ligne) {
            $validation = $this->validerLigneEquipement($ligne, $numeroLigne + 2);
            
            $preview[] = [
                'ligne' => $numeroLigne + 2,
                'donnees' => $ligne,
                'valide' => $validation['valide'],
                'erreurs' => $validation['erreurs'] ?? []
            ];
            
            if (!$validation['valide']) {
                $erreurs = array_merge($erreurs, $validation['erreurs']);
            }
        }

        return [
            'total_lignes' => count($donnees),
            'lignes_preview' => count($preview),
            'preview' => $preview,
            'erreurs_detectees' => count($erreurs),
            'structure_attendue' => $this->getStructureAttendue(),
            'mapping_colonnes' => $this->getMappingColonnes()
        ];
    }

    /**
     * Lire un fichier CSV/Excel et retourner les données
     * 
     * @param UploadedFile $file
     * @return array
     */
    protected function lireFichier(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        if ($extension === 'csv') {
            return $this->lireFichierCSV($file);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            // TODO: Implémenter la lecture Excel avec PhpSpreadsheet
            throw new \Exception('Support Excel en cours d\'implémentation');
        } else {
            throw new \Exception('Format de fichier non supporté');
        }
    }

    /**
     * Lire un fichier CSV
     * 
     * @param UploadedFile $file
     * @return array
     */
    protected function lireFichierCSV(UploadedFile $file): array
    {
        $donnees = [];
        $handle = fopen($file->getRealPath(), 'r');
        
        if ($handle === false) {
            throw new \Exception('Impossible de lire le fichier CSV');
        }

        // Lire la première ligne (headers)
        $headers = fgetcsv($handle, 0, ';'); // Délimiteur point-virgule par défaut
        
        if ($headers === false) {
            fclose($handle);
            throw new \Exception('Impossible de lire les en-têtes du fichier CSV');
        }

        // Nettoyer et normaliser les headers
        $headers = array_map(function($header) {
            return trim(strtolower($header));
        }, $headers);

        // Lire les données ligne par ligne
        while (($ligne = fgetcsv($handle, 0, ';')) !== false) {
            if (count($ligne) === count($headers)) {
                $donnees[] = array_combine($headers, $ligne);
            }
        }

        fclose($handle);
        return $donnees;
    }

    /**
     * Traiter une ligne de données pour créer un équipement
     * 
     * @param array $ligne
     * @param int $numeroLigne
     * @param int $userId
     * @param Agence $agence
     */
    protected function traiterLigneEquipement(array $ligne, int $numeroLigne, int $userId, Agence $agence): void
    {
        try {
            $validation = $this->validerLigneEquipement($ligne, $numeroLigne);
            
            if (!$validation['valide']) {
                $this->erreurs[] = [
                    'ligne' => $numeroLigne,
                    'erreurs' => $validation['erreurs']
                ];
                return;
            }

            // Mapper les données selon le format attendu
            $donneesEquipement = $this->mapperDonneesEquipement($ligne, $agence);
            
            // Créer l'équipement
            $equipement = Equipement::create($donneesEquipement);
            
            // Générer le QR code
            $equipement->generateQRCode();
            
            // Créer le mouvement initial
            $equipement->createMouvement(
                'import',
                "Import automatique - Ligne {$numeroLigne}",
                $userId
            );

            $this->succes[] = [
                'ligne' => $numeroLigne,
                'equipement_id' => $equipement->id,
                'reference' => $equipement->reference
            ];

        } catch (\Exception $e) {
            $this->erreurs[] = [
                'ligne' => $numeroLigne,
                'erreurs' => ['Erreur lors de la création: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Valider une ligne de données
     * 
     * @param array $ligne
     * @param int $numeroLigne
     * @return array
     */
    protected function validerLigneEquipement(array $ligne, int $numeroLigne): array
    {
        $mapping = $this->getMappingColonnes();
        $erreurs = [];

        // Vérifier les champs obligatoires
        $champsObligatoires = ['reference', 'numero_serie', 'code_inventaire', 'categorie'];
        
        foreach ($champsObligatoires as $champ) {
            $colonneCSV = array_search($champ, $mapping);
            if (!$colonneCSV || empty(trim($ligne[$colonneCSV] ?? ''))) {
                $erreurs[] = "Champ obligatoire manquant: {$champ}";
            }
        }

        // Vérifier l'unicité des références
        if (!empty($ligne['reference'] ?? '')) {
            if (Equipement::where('reference', trim($ligne['reference']))->exists()) {
                $erreurs[] = "Référence déjà existante: " . trim($ligne['reference']);
            }
        }

        if (!empty($ligne['numero_serie'] ?? '')) {
            if (Equipement::where('numero_serie', trim($ligne['numero_serie']))->exists()) {
                $erreurs[] = "Numéro de série déjà existant: " . trim($ligne['numero_serie']);
            }
        }

        if (!empty($ligne['code_inventaire'] ?? '')) {
            if (Equipement::where('code_inventaire', trim($ligne['code_inventaire']))->exists()) {
                $erreurs[] = "Code inventaire déjà existant: " . trim($ligne['code_inventaire']);
            }
        }

        // Vérifier que la catégorie existe
        if (!empty($ligne['categorie'] ?? '')) {
            $categorie = Categorie::where('nom', 'LIKE', '%' . trim($ligne['categorie']) . '%')->first();
            if (!$categorie) {
                $erreurs[] = "Catégorie introuvable: " . trim($ligne['categorie']);
            }
        }

        // Validation des formats
        if (!empty($ligne['prix_achat'] ?? '') && !is_numeric(str_replace(',', '.', $ligne['prix_achat']))) {
            $erreurs[] = "Prix d'achat invalide: " . $ligne['prix_achat'];
        }

        if (!empty($ligne['date_acquisition'] ?? '') && !strtotime($ligne['date_acquisition'])) {
            $erreurs[] = "Date d'acquisition invalide: " . $ligne['date_acquisition'];
        }

        return [
            'valide' => empty($erreurs),
            'erreurs' => $erreurs
        ];
    }

    /**
     * Mapper les données CSV vers le format Equipement
     * 
     * @param array $ligne
     * @param Agence $agence
     * @return array
     */
    protected function mapperDonneesEquipement(array $ligne, Agence $agence): array
    {
        // Récupérer la catégorie
        $categorie = Categorie::where('nom', 'LIKE', '%' . trim($ligne['categorie']) . '%')->first();
        
        return [
            'reference' => trim($ligne['reference']),
            'numero_serie' => trim($ligne['numero_serie']),
            'imei' => !empty($ligne['imei']) ? trim($ligne['imei']) : null,
            'code_inventaire' => trim($ligne['code_inventaire']),
            'marque' => !empty($ligne['marque']) ? trim($ligne['marque']) : null,
            'modele' => !empty($ligne['modele']) ? trim($ligne['modele']) : null,
            'categorie_id' => $categorie->id,
            'fournisseur' => !empty($ligne['fournisseur']) ? trim($ligne['fournisseur']) : null,
            'date_acquisition' => !empty($ligne['date_acquisition']) ? date('Y-m-d', strtotime($ligne['date_acquisition'])) : null,
            'prix_achat' => !empty($ligne['prix_achat']) ? floatval(str_replace(',', '.', $ligne['prix_achat'])) : null,
            'garantie_date_fin' => !empty($ligne['garantie_date_fin']) ? date('Y-m-d', strtotime($ligne['garantie_date_fin'])) : null,
            'etat' => !empty($ligne['etat']) ? trim($ligne['etat']) : 'neuf',
            'localisation' => !empty($ligne['localisation']) ? trim($ligne['localisation']) : 'Stock',
            'agence_proprietaire_id' => $agence->id,
            'agence_actuelle_id' => $agence->id,
            'statut_global' => $agence->type === 'generale' ? 'en_stock_general' : 'en_stock_local',
        ];
    }

    /**
     * Obtenir la structure attendue du fichier
     * 
     * @return array
     */
    public function getStructureAttendue(): array
    {
        return [
            'reference' => 'Référence unique de l\'équipement (obligatoire)',
            'numero_serie' => 'Numéro de série unique (obligatoire)', 
            'imei' => 'Code IMEI pour les appareils mobiles (optionnel)',
            'code_inventaire' => 'Code inventaire unique (obligatoire)',
            'marque' => 'Marque de l\'équipement (optionnel)',
            'modele' => 'Modèle de l\'équipement (optionnel)',
            'categorie' => 'Nom de la catégorie existante (obligatoire)',
            'fournisseur' => 'Nom du fournisseur (optionnel)',
            'date_acquisition' => 'Date d\'acquisition format JJ/MM/AAAA (optionnel)',
            'prix_achat' => 'Prix d\'achat en euros (optionnel)',
            'garantie_date_fin' => 'Date de fin de garantie format JJ/MM/AAAA (optionnel)',
            'etat' => 'État: neuf, en_service, en_panne, etc. (optionnel, défaut: neuf)',
            'localisation' => 'Localisation physique (optionnel, défaut: Stock)',
        ];
    }

    /**
     * Obtenir le mapping des colonnes CSV vers les champs
     * 
     * @return array
     */
    public function getMappingColonnes(): array
    {
        return [
            'reference' => 'reference',
            'numéro série' => 'numero_serie', 
            'numero serie' => 'numero_serie',
            'imei' => 'imei',
            'code inventaire' => 'code_inventaire',
            'inventaire' => 'code_inventaire',
            'marque' => 'marque',
            'modèle' => 'modele',
            'modele' => 'modele',
            'catégorie' => 'categorie',
            'categorie' => 'categorie',
            'fournisseur' => 'fournisseur',
            'date acquisition' => 'date_acquisition',
            'date d\'acquisition' => 'date_acquisition',
            'prix achat' => 'prix_achat',
            'prix d\'achat' => 'prix_achat',
            'prix' => 'prix_achat',
            'garantie' => 'garantie_date_fin',
            'fin garantie' => 'garantie_date_fin',
            'date garantie' => 'garantie_date_fin',
            'état' => 'etat',
            'etat' => 'etat',
            'localisation' => 'localisation',
            'lieu' => 'localisation',
        ];
    }

    /**
     * Générer un template CSV pour l'import
     * 
     * @return string
     */
    public function genererTemplateCSV(): string
    {
        $headers = [
            'reference',
            'numero_serie', 
            'imei',
            'code_inventaire',
            'marque',
            'modele',
            'categorie',
            'fournisseur',
            'date_acquisition',
            'prix_achat',
            'garantie_date_fin',
            'etat',
            'localisation'
        ];

        $exemples = [
            'EQ-2026-001',
            'SN123456789',
            '123456789012345',
            'INV-001',
            'Zebra',
            'MC3300',
            'PDA',
            'Zebra Technologies',
            '01/01/2026',
            '850.00',
            '01/01/2028',
            'neuf',
            'Stock Central'
        ];

        $csv = implode(';', $headers) . "\n";
        $csv .= implode(';', $exemples) . "\n";

        return $csv;
    }

    /**
     * Réinitialiser les compteurs
     */
    protected function resetCounters(): void
    {
        $this->erreurs = [];
        $this->succes = [];
        $this->lignesTraitees = 0;
    }
}