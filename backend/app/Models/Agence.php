<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agence extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'parent_id',
        'nom',
        'adresse',
        'ville',
        'responsable_id',
        'gestionnaire_stock_id',
        'statut',
    ];

    // Relations
    public function parent()
    {
        return $this->belongsTo(Agence::class, 'parent_id');
    }

    public function sousAgences()
    {
        return $this->hasMany(Agence::class, 'parent_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function gestionnaireStock()
    {
        return $this->belongsTo(User::class, 'gestionnaire_stock_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function equipementsProprietaires()
    {
        return $this->hasMany(Equipement::class, 'agence_proprietaire_id');
    }

    public function equipementsActuels()
    {
        return $this->hasMany(Equipement::class, 'agence_actuelle_id');
    }

    public function transfertsSource()
    {
        return $this->hasMany(Transfert::class, 'agence_source_id');
    }

    public function transfertsDestination()
    {
        return $this->hasMany(Transfert::class, 'agence_destination_id');
    }

    public function demandesMateriel()
    {
        return $this->hasMany(DemandeMateriel::class);
    }
}