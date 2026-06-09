<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'reference',
        'numero_serie',
        'imei',
        'code_inventaire',
        'marque',
        'modele',
        'categorie_id',
        'fournisseur',
        'date_acquisition',
        'prix_achat',
        'garantie_date_fin',
        'etat',
        'localisation',
        'agence_proprietaire_id',
        'agence_actuelle_id',
        'statut_global',
        'photo',
        'qr_code',
    ];

    // Relations
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function agenceProprietaire()
    {
        return $this->belongsTo(Agence::class, 'agence_proprietaire_id');
    }

    public function agenceActuelle()
    {
        return $this->belongsTo(Agence::class, 'agence_actuelle_id');
    }

    public function consommables()
    {
        return $this->hasMany(Consommable::class);
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    public function mouvements()
    {
        return $this->hasMany(Mouvement::class);
    }

    public function pannes()
    {
        return $this->hasMany(Panne::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function pertes()
    {
        return $this->hasMany(Perte::class);
    }

    public function transferts()
    {
        return $this->hasMany(Transfert::class);
    }

    /**
     * Marquer l'équipement en panne
     * 
     * @return void
     */
    public function marquerEnPanne(): void
    {
        $this->update(['statut_global' => 'en_panne']);
    }
}