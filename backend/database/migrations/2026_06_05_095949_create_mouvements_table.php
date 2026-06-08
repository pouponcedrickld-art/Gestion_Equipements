<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements', function (Blueprint $table) {
            $table->id();
            $table->enum('type_mouvement', ['affectation', 'retour', 'changement_etat', 'maintenance', 'reforme', 'perte', 'creation', 'modification', 'transfert', 'acquisition']);
            $table->foreignId('equipement_id')->constrained('equipements');
            $table->foreignId('agent_id')->nullable()->constrained('agents');
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('date_mouvement');
            $table->json('ancienne_valeur')->nullable();
            $table->json('nouvelle_valeur')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements');
    }
};
