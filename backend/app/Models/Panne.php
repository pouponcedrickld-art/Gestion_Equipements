<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panne extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipement_id',
        'agent_id',
        'gestionnaire_stock_id',
        'technicien_id',
        'date_declaration',
        'description',
        'niveau_gravite',
        'photos',
        'statut',
        'diagnostic_technicien',
        'action_realisee',
        'cout_reparation',
        'date_resolution',
        'decision_finale',
    ];

    protected $casts = [
        'date_declaration' => 'datetime',
        'photos' => 'array',
        'cout_reparation' => 'decimal:2',
        'date_resolution' => 'datetime',
    ];

    // Relations
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function gestionnaireStock()
    {
        return $this->belongsTo(User::class, 'gestionnaire_stock_id');
    }

    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Historique des transitions de statut d’une panne.
     */
    public function statusHistories()
    {
        return $this->hasMany(PanneStatusHistory::class)->orderBy('created_at', 'desc');
    }
}