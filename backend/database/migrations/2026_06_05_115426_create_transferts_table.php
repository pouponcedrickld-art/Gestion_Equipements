<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipement_id')->constrained('equipements');
            $table->foreignId('agence_source_id')->constrained('agences');
            $table->foreignId('agence_destination_id')->constrained('agences');
            $table->enum('type_transfert', ['livraison_generale', 'retour_generale', 'transfert_interne'])->default('livraison_generale');
            $table->foreignId('demande_par_id')->constrained('users');
            $table->foreignId('valide_par_id')->nullable()->constrained('users');
            $table->dateTime('date_demande');
            $table->dateTime('date_expedition')->nullable();
            $table->dateTime('date_reception')->nullable();
            $table->enum('statut', ['demande', 'approuve', 'expedie', 'recu', 'refuse'])->default('demande');
            $table->integer('quantite')->default(1);
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferts');
    }
};
