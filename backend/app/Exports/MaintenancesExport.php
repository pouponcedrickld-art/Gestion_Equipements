<?php

namespace App\Exports;

use App\Models\Maintenance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

class MaintenancesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithChunkReading, ShouldQueue
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Maintenance::with(['equipement', 'technicienUser', 'panne']);

        if (isset($this->filters['agence_id'])) {
            $query->whereHas('equipement', function($q) {
                $q->where('agence_actuelle_id', $this->filters['agence_id']);
            });
        }
        if (isset($this->filters['type_maintenance'])) {
            $query->where('type_maintenance', $this->filters['type_maintenance']);
        }
        if (isset($this->filters['statut'])) {
            $query->where('statut', $this->filters['statut']);
        }
        if (isset($this->filters['date_debut']) && isset($this->filters['date_fin'])) {
            $query->whereBetween('date_prevue', [$this->filters['date_debut'], $this->filters['date_fin']]);
        }

        return $query->get();
    }

    public function map($maintenance): array
    {
        return [
            $maintenance->id,
            $maintenance->equipement?->reference ?? 'N/A',
            $maintenance->equipement?->nom ?? 'N/A',
            $maintenance->type_maintenance,
            $maintenance->statut,
            $maintenance->date_prevue,
            $maintenance->date_debut,
            $maintenance->date_fin,
            $maintenance->technicienUser?->name ?? 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Référence Equipement',
            'Nom Equipement',
            'Type',
            'Statut',
            'Date Prévue',
            'Date Début',
            'Date Fin',
            'Technicien',
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
