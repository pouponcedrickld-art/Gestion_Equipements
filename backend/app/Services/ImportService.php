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
    /**
     * Prévisualiser l'importation
     */
    public function previewImport(UploadedFile $file, int $maxPreview = 10): array
    {
        $rows = $this->parseCsv($file);
        $preview = [];
        $errorsCount = 0;

        foreach (array_slice($rows, 0, $maxPreview) as $index => $row) {
            $validation = $this->validateRow($row);
            if (!$validation['success']) $errorsCount++;
            
            $preview[] = [
                'row' => $index + 2,
                'data' => $row,
                'valid' => $validation['success'],
                'errors' => $validation['errors']
            ];
        }

        return [
            'total_rows' => count($rows),
            'preview' => $preview,
            'errors_count' => $errorsCount
        ];
    }

    /**
     * Exécuter l'importation
     */
    public function importEquipements(UploadedFile $file, int $userId, ?int $agenceId = null): array
    {
        $rows = $this->parseCsv($file);
        $success = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $validation = $this->validateRow($row);
                if (!$validation['success']) {
                    $errors[] = ['row' => $index + 2, 'errors' => $validation['errors']];
                    continue;
                }

                // Création de l'équipement
                $equipement = Equipement::create([
                    'nom' => $row['nom'],
                    'reference' => $row['reference'] ?? ('REF-' . strtoupper(Str::random(6))),
                    'marque' => $row['marque'] ?? null,
                    'modele' => $row['modele'] ?? null,
                    'numero_serie' => $row['numero_serie'],
                    'categorie_id' => $this->findCategorieId($row['categorie_id']),
                    'agence_proprietaire_id' => $row['agence_proprietaire_id'] ?? ($agenceId ?? Agence::where('type', 'generale')->first()->id),
                    'agence_actuelle_id' => $row['agence_actuelle_id'] ?? ($agenceId ?? Agence::where('type', 'generale')->first()->id),
                    'statut_global' => $this->mapStatut($row['statut'] ?? 'actif'),
                    'etat' => $row['statut'] ?? 'actif',
                    'localisation' => $row['localisation'] ?? null,
                    'code_inventaire' => $row['code_inventaire'] ?? strtoupper(Str::random(10))
                ]);

                $equipement->generateQRCode();
                $success++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success' => true,
            'message' => "Import terminé avec $success succès",
            'statistiques' => [
                'lignes_traitees' => count($rows),
                'succes' => $success,
                'erreurs' => count($errors),
            ],
            'details' => [
                'erreurs' => $errors,
            ]
        ];
    }

    /**
     * Générer un template CSV
     */
    public function genererTemplateCSV(): string
    {
        $headers = ['nom', 'categorie_id', 'numero_serie', 'agence_proprietaire_id', 'agence_actuelle_id', 'statut', 'localisation', 'marque', 'modele'];
        $example = ['Ordinateur Portable HP', '1', 'SN123456789', '1', '1', 'actif', 'Bâtiment A, Bureau 101', 'HP', 'EliteBook'];
        
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers, ';');
        fputcsv($output, $example, ';');
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    protected function parseCsv(UploadedFile $file): array
    {
        $data = [];
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ",");
            if (!$headers) {
                rewind($handle);
                $headers = fgetcsv($handle, 1000, ";");
            }

            if (!$headers) {
                fclose($handle);
                return [];
            }

            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE || ($row = fgetcsv($handle, 1000, ";"))) {
                if (!$row || count($row) !== count($headers)) continue;
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    protected function validateRow(array $row): array
    {
        $validator = Validator::make($row, [
            'nom' => 'required|string',
            'numero_serie' => 'required|string|unique:equipements,numero_serie',
            'categorie_id' => 'required',
            'statut' => 'nullable|string|in:nouveau,actif,en_maintenance,hors_service,archive',
        ]);

        return [
            'success' => !$validator->fails(),
            'errors' => $validator->errors()->all()
        ];
    }

    protected function findCategorieId($value): int
    {
        if (is_numeric($value)) {
            $cat = Categorie::find($value);
            if ($cat) return $cat->id;
        }

        $cat = Categorie::where('nom', 'like', "%$value%")->first();
        if (!$cat) {
            $cat = Categorie::create(['nom' => $value, 'description' => 'Créé par import']);
        }
        return $cat->id;
    }

    protected function mapStatut(string $statut): string
    {
        $map = [
            'nouveau' => 'en_stock_general',
            'actif' => 'en_service',
            'en_maintenance' => 'en_maintenance',
            'hors_service' => 'reforme',
            'archive' => 'archive'
        ];

        return $map[$statut] ?? 'en_service';
    }
}
