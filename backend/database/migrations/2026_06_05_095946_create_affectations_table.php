<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents');
            $table->foreignId('equipement_id')->constrained('equipements');
            $table->dateTime('date_affectation');
            $table->dateTime('date_retour_prevu')->nullable();
            $table->dateTime('date_retour_effectif')->nullable();
            $table->foreignId('affecte_par')->constrained('users');
            $table->enum('etat_retour', ['bon', 'abime', 'non_fonctionnel'])->nullable();
            $table->text('observations')->nullable();
            $table->string('pv_remise_path')->nullable();
            $table->enum('statut', ['active', 'retournee', 'expiree'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
