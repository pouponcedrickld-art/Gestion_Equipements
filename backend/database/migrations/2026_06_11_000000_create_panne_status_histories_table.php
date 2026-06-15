<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panne_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('panne_id')->constrained('pannes')->cascadeOnDelete();

            // Historisation de la transition
            $table->string('statut_ancien')->nullable();
            $table->string('statut_nouveau');

            // Données métier optionnelles (dupliquees pour l’historique)
            $table->text('commentaire')->nullable();
            $table->text('action_realisee')->nullable();
            $table->decimal('cout_reparation', 10, 2)->nullable();

            // Qui a déclenché la transition
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Pour éviter des doublons involontaires (optionnel mais utile)
            $table->index(['panne_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panne_status_histories');
    }
};

