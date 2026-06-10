<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'panne_id',
        'equipement_id',
        'technicien_id',
        'type_maintenance',
        'date_prevue',
        'responsable',
        'technicien',
        'diagnostic',
        'cout',
        'date_debut',
        'date_fin',
        'observations',
        'statut',
    ];

    protected $casts = [
        'date_prevue' => 'datetime',
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'cout' => 'decimal:2',
    ];

    // Relations
    public function panne()
    {
        return $this->belongsTo(Panne::class);
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function technicienUser()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }
}