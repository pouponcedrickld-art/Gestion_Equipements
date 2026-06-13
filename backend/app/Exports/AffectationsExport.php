<?php

namespace App\Exports;

use App\Models\Affectation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

class AffectationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithChunkReading, ShouldQueue
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Affectation::with(['equipement', 'agent', 'agence']);

        if (isset($this->filters['agence_id'])) {
            $query->where('agence_id', $this->filters['agence_id']);
        }
        if (isset($this->filters['date_debut']) && isset($this->filters['date_fin'])) {
            $query->whereBetween('date_affectation', [$this->filters['date_debut'], $this->filters['date_fin']]);
        }

        return $query->get();
    }

    public function map($affectation): array
    {
        return [
            $affectation->id,
            $affectation->equipement?->reference ?? 'N/A',
            $affectation->equipement?->nom ?? 'N/A',
            $affectation->agent?->nom ?? 'N/A',
            $affectation->agent?->prenom ?? 'N/A',
            $affectation->agence?->nom ?? 'N/A',
            $affectation->date_affectation,
            $affectation->date_retour,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Référence Equipement',
            'Nom Equipement',
            'Nom Agent',
            'Prénom Agent',
            'Agence',
            'Date Affectation',
            'Date Retour',
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
