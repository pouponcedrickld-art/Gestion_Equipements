<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Ajouter la colonne equipement_id après id
            $table->foreignId('equipement_id')->nullable()->after('id')->constrained('equipements')->onDelete('cascade');
            
            // Ajouter la colonne panne_id après equipement_id
            $table->foreignId('panne_id')->nullable()->after('equipement_id')->constrained('pannes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Supprimer les foreign keys d'abord
            $table->dropForeign(['equipement_id']);
            $table->dropForeign(['panne_id']);
            
            // Puis supprimer les colonnes
            $table->dropColumn(['equipement_id', 'panne_id']);
        });
    }
};
