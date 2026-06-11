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
            // Ajouter la colonne equipement_id seulement si elle n'existe pas
            if (!Schema::hasColumn('maintenances', 'equipement_id')) {
                $table->foreignId('equipement_id')->nullable()->after('id')->constrained('equipements')->onDelete('cascade');
            }
            
            // Ajouter la colonne panne_id seulement si elle n'existe pas
            if (!Schema::hasColumn('maintenances', 'panne_id')) {
                $table->foreignId('panne_id')->nullable()->after('equipement_id')->constrained('pannes')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Vérifier si les colonnes existent avant de les supprimer
            if (Schema::hasColumn('maintenances', 'equipement_id')) {
                $table->dropForeign(['equipement_id']);
                $table->dropColumn('equipement_id');
            }
            
            if (Schema::hasColumn('maintenances', 'panne_id')) {
                $table->dropForeign(['panne_id']);
                $table->dropColumn('panne_id');
            }
        });
    }
};
