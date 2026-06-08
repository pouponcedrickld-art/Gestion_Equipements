<?php

namespace App\Services;

use App\Models\Equipement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QRCodeService
{
    protected $qrCodePath = 'public/qr-codes';
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.url');
    }

    /**
     * Générer un QR code pour un équipement
     * 
     * @param Equipement $equipement
     * @param array $options
     * @return string Chemin du fichier QR généré
     */
    public function genererQREquipement(Equipement $equipement, array $options = []): string
    {
        // Données à encoder dans le QR code
        $qrData = [
            'type' => 'equipement',
            'id' => $equipement->id,
            'reference' => $equipement->reference,
            'numero_serie' => $equipement->numero_serie,
            'url' => $this->baseUrl . "/equipements/{$equipement->id}",
            'generated_at' => now()->toISOString()
        ];

        // Options par défaut
        $defaultOptions = [
            'size' => 200,
            'margin' => 10,
            'format' => 'png',
            'error_correction' => 'M', // L, M, Q, H
        ];

        $options = array_merge($defaultOptions, $options);

        // Générer le nom du fichier
        $filename = $this->genererNomFichier($equipement, $options['format']);
        
        // Créer le QR code
        $qrCodeContent = $this->creerQRCode(json_encode($qrData), $options);
        
        // Sauvegarder le fichier
        $cheminComplet = $this->qrCodePath . '/' . $filename;
        Storage::put($cheminComplet, $qrCodeContent);

        return $filename;
    }

    /**
     * Générer un QR code en lot pour plusieurs équipements
     * 
     * @param array $equipementIds
     * @param array $options
     * @return array
     */
    public function genererQREnLot(array $equipementIds, array $options = []): array
    {
        $resultats = [];
        
        foreach ($equipementIds as $equipementId) {
            try {
                $equipement = Equipement::findOrFail($equipementId);
                $filename = $this->genererQREquipement($equipement, $options);
                
                $resultats[] = [
                    'equipement_id' => $equipementId,
                    'reference' => $equipement->reference,
                    'qr_code' => $filename,
                    'success' => true
                ];
                
                // Mettre à jour l'équipement
                $equipement->update(['qr_code' => $filename]);
                
            } catch (\Exception $e) {
                $resultats[] = [
                    'equipement_id' => $equipementId,
                    'error' => $e->getMessage(),
                    'success' => false
                ];
            }
        }

        return $resultats;
    }

    /**
     * Créer un QR code personnalisé avec du texte
     * 
     * @param string $text
     * @param array $options
     * @return string Contenu du QR code
     */
    public function creerQRCustom(string $text, array $options = []): string
    {
        // Options par défaut
        $defaultOptions = [
            'size' => 200,
            'margin' => 10,
            'format' => 'png',
            'error_correction' => 'M',
        ];

        $options = array_merge($defaultOptions, $options);
        
        return $this->creerQRCode($text, $options);
    }

    /**
     * Vérifier si un QR code existe
     * 
     * @param string $filename
     * @return bool
     */
    public function qrCodeExiste(string $filename): bool
    {
        return Storage::exists($this->qrCodePath . '/' . $filename);
    }

    /**
     * Supprimer un QR code
     * 
     * @param string $filename
     * @return bool
     */
    public function supprimerQRCode(string $filename): bool
    {
        if ($this->qrCodeExiste($filename)) {
            return Storage::delete($this->qrCodePath . '/' . $filename);
        }
        
        return true;
    }

    /**
     * Obtenir l'URL publique d'un QR code
     * 
     * @param string $filename
     * @return string|null
     */
    public function getUrlQRCode(string $filename): ?string
    {
        if ($this->qrCodeExiste($filename)) {
            return Storage::url($this->qrCodePath . '/' . $filename);
        }
        
        return null;
    }

    /**
     * Nettoyer les anciens QR codes (plus de 30 jours)
     * 
     * @return array
     */
    public function nettoyerAnciensQRCodes(): array
    {
        $fichiers = Storage::files($this->qrCodePath);
        $supprimes = [];
        $dateLimit = now()->subDays(30);

        foreach ($fichiers as $fichier) {
            $dateModification = Storage::lastModified($fichier);
            
            if ($dateModification < $dateLimit->timestamp) {
                // Vérifier si le QR est encore utilisé
                $filename = basename($fichier);
                $enUtilisation = Equipement::where('qr_code', $filename)->exists();
                
                if (!$enUtilisation) {
                    Storage::delete($fichier);
                    $supprimes[] = $filename;
                }
            }
        }

        return [
            'supprimes' => count($supprimes),
            'fichiers' => $supprimes
        ];
    }

    /**
     * Décoder les données d'un QR code équipement
     * 
     * @param string $qrContent
     * @return array|null
     */
    public function decoderQREquipement(string $qrContent): ?array
    {
        try {
            $data = json_decode($qrContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            // Vérifier que c'est bien un QR d'équipement
            if (!isset($data['type']) || $data['type'] !== 'equipement') {
                return null;
            }

            return $data;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Générer le nom du fichier QR
     * 
     * @param Equipement $equipement
     * @param string $format
     * @return string
     */
    protected function genererNomFichier(Equipement $equipement, string $format): string
    {
        $reference = Str::slug($equipement->reference);
        $timestamp = time();
        $random = Str::random(6);
        
        return "qr_{$reference}_{$timestamp}_{$random}.{$format}";
    }

    /**
     * Créer le contenu du QR code
     * Note: Implémentation basique - remplacer par une vraie librairie QR
     * 
     * @param string $data
     * @param array $options
     * @return string
     */
    protected function creerQRCode(string $data, array $options): string
    {
        // TODO: Remplacer par une vraie implémentation QR
        // Pour l'instant, on simule la génération d'un QR code
        
        $size = $options['size'];
        
        // Créer une image simple comme placeholder
        $image = imagecreate($size, $size);
        
        // Couleurs
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Fond blanc
        imagefill($image, 0, 0, $white);
        
        // Simuler un pattern QR basique
        $gridSize = 10;
        $cellSize = $size / $gridSize;
        
        for ($i = 0; $i < $gridSize; $i++) {
            for ($j = 0; $j < $gridSize; $j++) {
                // Pattern pseudo-aléatoire basé sur les données
                if ((crc32($data . $i . $j) % 3) === 0) {
                    imagefilledrectangle(
                        $image,
                        $j * $cellSize,
                        $i * $cellSize,
                        ($j + 1) * $cellSize,
                        ($i + 1) * $cellSize,
                        $black
                    );
                }
            }
        }
        
        // Ajouter les coins de détection (carrés noirs)
        $cornerSize = $cellSize * 3;
        
        // Coin supérieur gauche
        imagefilledrectangle($image, 0, 0, $cornerSize, $cornerSize, $black);
        imagefilledrectangle($image, $cellSize, $cellSize, $cornerSize - $cellSize, $cornerSize - $cellSize, $white);
        
        // Coin supérieur droit
        imagefilledrectangle($image, $size - $cornerSize, 0, $size, $cornerSize, $black);
        imagefilledrectangle($image, $size - $cornerSize + $cellSize, $cellSize, $size - $cellSize, $cornerSize - $cellSize, $white);
        
        // Coin inférieur gauche
        imagefilledrectangle($image, 0, $size - $cornerSize, $cornerSize, $size, $black);
        imagefilledrectangle($image, $cellSize, $size - $cornerSize + $cellSize, $cornerSize - $cellSize, $size - $cellSize, $white);
        
        // Capturer l'image en PNG
        ob_start();
        imagepng($image);
        $imageContent = ob_get_contents();
        ob_end_clean();
        
        // Nettoyer
        imagedestroy($image);
        
        return $imageContent;
    }

    /**
     * Obtenir les statistiques des QR codes
     * 
     * @return array
     */
    public function getStatistiques(): array
    {
        $fichiers = Storage::files($this->qrCodePath);
        
        return [
            'total_qr_codes' => count($fichiers),
            'equipements_avec_qr' => Equipement::whereNotNull('qr_code')->count(),
            'taille_totale' => array_sum(array_map(fn($f) => Storage::size($f), $fichiers)),
            'dernier_genere' => count($fichiers) > 0 ? 
                max(array_map(fn($f) => Storage::lastModified($f), $fichiers)) : null,
        ];
    }
}