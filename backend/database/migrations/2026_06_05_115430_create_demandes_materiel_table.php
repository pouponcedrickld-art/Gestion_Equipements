<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_materiel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences');
            $table->foreignId('chef_agence_id')->constrained('users');
            $table->foreignId('equipement_id')->constrained('equipements'); // Type de matériel
            $table->integer('quantite')->default(1);
            $table->enum('urgence', ['Basse', 'Moyenne', 'Haute'])->default('Basse');
            $table->text('motif');
            $table->date('date_souhaitee');
            $table->enum('statut', ['en attente', 'approuvé', 'rejeté'])->default('en attente');
            $table->foreignId('traite_par_id')->nullable()->constrained('users');
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_materiel');
    }
};
