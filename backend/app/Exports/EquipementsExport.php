<?php

namespace App\Exports;

use App\Models\Equipement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

class EquipementsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithChunkReading, ShouldQueue
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Equipement::with(['categorie', 'agenceActuelle', 'user']);

        if (isset($this->filters['agence_id'])) {
            $query->where('agence_actuelle_id', $this->filters['agence_id']);
        }
        if (isset($this->filters['categorie_id'])) {
            $query->where('categorie_id', $this->filters['categorie_id']);
        }
        if (isset($this->filters['statut'])) {
            $query->where('statut_global', $this->filters['statut']);
        }

        return $query->get();
    }

    public function map($equipement): array
    {
        return [
            $equipement->id,
            $equipement->reference,
            $equipement->nom,
            $equipement->categorie?->nom ?? 'N/A',
            $equipement->agenceActuelle?->nom ?? 'N/A',
            $equipement->user?->name ?? 'Non affecté',
            $equipement->statut_global,
            $equipement->date_achat,
            $equipement->date_fin_garantie,
            $equipement->etat,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Référence',
            'Nom',
            'Catégorie',
            'Agence',
            'Affecté à',
            'Statut',
            'Date achat',
            'Fin garantie',
            'État',
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
