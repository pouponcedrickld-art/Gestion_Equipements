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
        'responsable_id',
        'documents',
        'specifications',
    ];

    protected $casts = [
        'date_acquisition' => 'date',
        'garantie_date_fin' => 'date',
        'prix_achat' => 'decimal:2',
        'documents' => 'array',
        'specifications' => 'array',
    ];

    // ===== RELATIONS =====

    /**
     * Relation : Équipement appartient à une catégorie
     */
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    /**
     * Relation : Agence propriétaire de l'équipement
     */
    public function agenceProprietaire()
    {
        return $this->belongsTo(Agence::class, 'agence_proprietaire_id');
    }

    /**
     * Relation : Agence où se trouve actuellement l'équipement
     */
    public function agenceActuelle()
    {
        return $this->belongsTo(Agence::class, 'agence_actuelle_id');
    }

    /**
     * Relation : Responsable de l'équipement
     */
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    /**
     * Relation : Consommables liés à cet équipement
     */
    public function consommables()
    {
        return $this->hasMany(Consommable::class);
    }

    /**
     * Relation : Affectations de cet équipement
     */
    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    /**
     * Relation : Mouvements de cet équipement
     */
    public function mouvements()
    {
        return $this->hasMany(Mouvement::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relation : Pannes déclarées sur cet équipement
     */
    public function pannes()
    {
        return $this->hasMany(Panne::class);
    }

    /**
     * Relation : Maintenances effectuées sur cet équipement
     */
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Relation : Pertes déclarées pour cet équipement
     */
    public function pertes()
    {
        return $this->hasMany(Perte::class);
    }

    /**
     * Relation : Transferts impliquant cet équipement
     */
    public function transferts()
    {
        return $this->hasMany(Transfert::class);
    }

    /**
     * Relation : Affectation active (en cours)
     */
    public function affectationActive()
    {
        return $this->hasOne(Affectation::class)->where('statut', 'active');
    }

    // ===== SCOPES =====

    /**
     * Scope : Filtrer par agence actuelle
     */
    public function scopeByAgence($query, $agenceId)
    {
        return $query->where('agence_actuelle_id', $agenceId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('nom', 'like', '%' . $term . '%')
              ->orWhere('numero_serie', 'like', '%' . $term . '%')
              ->orWhere('code_inventaire', 'like', '%' . $term . '%')
              ->orWhere('reference', 'like', '%' . $term . '%');
        });
    }

    /**
     * Scope : Filtrer par statut (pastille couleur)
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('etat', $statut);
    }

    /**
     * Scope : Filtrer par catégorie
     */
    public function scopeByCategorie($query, $categorieId)
    {
        return $query->where('categorie_id', $categorieId);
    }

    /**
     * Scope : Équipements disponibles pour transfert
     */
    public function scopeDisponiblesTransfert($query)
    {
        return $query->whereIn('statut_global', ['en_stock_general', 'en_stock_local']);
    }

    /**
     * Scope : Équipements proches de la fin de garantie
     */
    public function scopeGarantieExpireSoon($query, $jours = 30)
    {
        return $query->whereNotNull('garantie_date_fin')
                    ->whereBetween('garantie_date_fin', [now(), now()->addDays($jours)]);
    }

    // ===== MÉTHODES UTILITAIRES =====

    /**
     * Générer un QR code pour cet équipement
     */
    public function generateQRCode()
    {
        $qrService = app(\App\Services\QRCodeService::class);
        $filename = $qrService->genererQREquipement($this);
        
        $this->update(['qr_code' => $filename]);
        
        return $filename;
    }

    /**
     * Créer un mouvement pour cet équipement
     */
    public function createMouvement($type, $description, $userId, $ancienneValeur = null, $nouvelleValeur = null)
    {
        return $this->mouvements()->create([
            'type_mouvement' => $type,
            'user_id' => $userId,
            'date_mouvement' => now(),
            'ancienne_valeur' => $ancienneValeur,
            'nouvelle_valeur' => $nouvelleValeur,
            'description' => $description,
        ]);
    }

    /**
     * Changer l'agence actuelle avec traçabilité
     */
    public function changerAgence($nouvelleAgenceId, $userId, $description = null)
    {
        $ancienneAgence = $this->agence_actuelle_id;
        
        $this->update(['agence_actuelle_id' => $nouvelleAgenceId]);
        
        $this->createMouvement(
            'transfert',
            $description ?? "Transfert vers agence ID: {$nouvelleAgenceId}",
            $userId,
            ['agence_id' => $ancienneAgence],
            ['agence_id' => $nouvelleAgenceId]
        );
        
        return $this;
    }

    /**
     * Changer le statut avec traçabilité
     */
    public function changerStatut($nouveauStatut, $userId, $description = null)
    {
        $ancienStatut = $this->statut_global;
        
        $this->update(['statut_global' => $nouveauStatut]);
        
        $this->createMouvement(
            'changement_etat',
            $description ?? "Changement de statut: {$ancienStatut} → {$nouveauStatut}",
            $userId,
            ['statut' => $ancienStatut],
            ['statut' => $nouveauStatut]
        );
        
        return $this;
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

    /**
     * Vérifier si l'équipement est disponible pour affectation
     */
    public function isDisponiblePourAffectation()
    {
        return in_array($this->statut_global, ['en_stock_local', 'en_stock_general']) 
               && $this->etat === 'en_service';
    }

    /**
     * Obtenir le statut de la garantie
     */
    public function getStatutGarantieAttribute()
    {
        if (!$this->garantie_date_fin) return 'inconnue';
        
        $now = now();
        if ($this->garantie_date_fin < $now) return 'expiree';
        
        $joursRestants = $now->diffInDays($this->garantie_date_fin);
        if ($joursRestants <= 30) return 'expire_bientot';
        
        return 'valide';
    }

    /**
     * Obtenir l'affectation en cours
     */
    public function getAffectationEnCoursAttribute()
    {
        return $this->affectationActive()->first();
    }
}