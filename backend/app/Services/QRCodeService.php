<?php

namespace App\Services;

use App\Models\Equipement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
     */
    public function genererQREquipement(Equipement $equipement, array $options = []): string
    {
        // Données à encoder (JSON)
        $qrData = json_encode([
            'type' => 'equipement',
            'id' => $equipement->id,
            'reference' => $equipement->reference,
            'numero_serie' => $equipement->numero_serie,
            'url' => $this->baseUrl . "/equipements/{$equipement->id}",
            'generated_at' => now()->toISOString()
        ]);

        $filename = "qr_" . Str::slug($equipement->reference) . "_" . time() . ".png";
        
        // Utilisation de la librairie SimpleQRCode
        $qrCode = QrCode::format('png')
            ->size($options['size'] ?? 300)
            ->margin($options['margin'] ?? 1)
            ->errorCorrection('H')
            ->generate($qrData);
        
        Storage::put($this->qrCodePath . '/' . $filename, $qrCode);

        return $filename;
    }

    /**
     * Décoder les données d'un QR code
     */
    public function decoderQREquipement(string $qrContent): ?array
    {
        try {
            $data = json_decode($qrContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) return null;
            if (!isset($data['type']) || $data['type'] !== 'equipement') return null;
            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUrlQRCode(string $filename): ?string
    {
        if (Storage::exists($this->qrCodePath . '/' . $filename)) {
            return Storage::url($this->qrCodePath . '/' . $filename);
        }
        return null;
    }
}
