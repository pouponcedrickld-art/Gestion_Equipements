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
            $table->string('type_mission');
            $table->json('equipements_demandes');
            $table->date('date_besoin');
            $table->enum('statut', ['en_attente', 'approuve_partiel', 'approuve_total', 'refuse', 'livre'])->default('en_attente');
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
