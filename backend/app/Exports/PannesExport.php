<?php

namespace App\Exports;

use App\Models\Panne;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

class PannesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithChunkReading, ShouldQueue
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Panne::with(['equipement', 'equipement.agenceActuelle', 'user']);

        if (isset($this->filters['agence_id'])) {
            $query->whereHas('equipement', function($q) {
                $q->where('agence_actuelle_id', $this->filters['agence_id']);
            });
        }
        if (isset($this->filters['statut'])) {
            $query->where('statut', $this->filters['statut']);
        }
        if (isset($this->filters['date_debut']) && isset($this->filters['date_fin'])) {
            $query->whereBetween('date_declaration', [$this->filters['date_debut'], $this->filters['date_fin']]);
        }

        return $query->get();
    }

    public function map($panne): array
    {
        return [
            $panne->id,
            $panne->equipement?->reference ?? 'N/A',
            $panne->equipement?->nom ?? 'N/A',
            $panne->statut,
            $panne->description,
            $panne->equipement?->agenceActuelle?->nom ?? 'N/A',
            $panne->date_declaration,
            $panne->date_resolution,
            $panne->user?->name ?? 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Référence Equipement',
            'Nom Equipement',
            'Statut',
            'Description',
            'Agence',
            'Date Déclaration',
            'Date Résolution',
            'Déclaré par',
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
