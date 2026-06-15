<?php

namespace App\Exports;

use App\Models\Mouvement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

class MouvementsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithChunkReading, ShouldQueue
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Mouvement::with(['equipement', 'agenceDepart', 'agenceArrivee', 'user']);

        if (isset($this->filters['agence_id'])) {
            $query->where(function($q) {
                $q->where('agence_depart_id', $this->filters['agence_id'])
                  ->orWhere('agence_arrivee_id', $this->filters['agence_id']);
            });
        }
        if (isset($this->filters['date_debut']) && isset($this->filters['date_fin'])) {
            $query->whereBetween('date_mouvement', [$this->filters['date_debut'], $this->filters['date_fin']]);
        }

        return $query->get();
    }

    public function map($mouvement): array
    {
        return [
            $mouvement->id,
            $mouvement->type,
            $mouvement->equipement?->reference ?? 'N/A',
            $mouvement->equipement?->nom ?? 'N/A',
            $mouvement->agenceDepart?->nom ?? 'N/A',
            $mouvement->agenceArrivee?->nom ?? 'N/A',
            $mouvement->user?->name ?? 'N/A',
            $mouvement->date_mouvement,
            $mouvement->raison,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Type',
            'Référence Equipement',
            'Nom Equipement',
            'Agence Départ',
            'Agence Arrivée',
            'Réalisé par',
            'Date',
            'Raison',
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
