<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_agences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->onDelete('cascade');
            $table->foreignId('equipement_id')->nullable()->constrained('equipements')->onDelete('set null');
            $table->foreignId('categorie_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->integer('quantite')->default(0); // Quantité totale en stock
            $table->integer('quantite_disponible')->default(0); // Quantité disponible (non réservée)
            $table->integer('quantite_reservee')->default(0); // Quantité réservée (pour les demandes en attente)
            $table->timestamp('date_derniere_mise_a_jour')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index pour optimiser les requêtes
            $table->index('agence_id');
            $table->index('equipement_id');
            $table->index('categorie_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_agences');
    }
};
