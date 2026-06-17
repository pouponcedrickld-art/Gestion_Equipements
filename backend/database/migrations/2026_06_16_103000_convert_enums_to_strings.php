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
        // Convert status enums to strings to allow for more flexibility and avoid seeder/logic crashes
        Schema::table('pannes', function (Blueprint $table) {
            $table->string('statut')->default('declaree')->change();
            $table->string('niveau_gravite')->default('mineure')->change();
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('statut')->default('planifiee')->change();
        });

        Schema::table('affectations', function (Blueprint $table) {
            $table->string('statut')->default('active')->change();
            $table->string('etat_retour')->nullable()->change();
        });

        Schema::table('pertes', function (Blueprint $table) {
            $table->string('statut')->default('declaree')->change();
        });

        Schema::table('demandes_materiel', function (Blueprint $table) {
            $table->string('statut')->default('en attente')->change();
        });
        
        Schema::table('transferts', function (Blueprint $table) {
            $table->string('statut')->default('demande')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting to original enums if possible (might lose data if new statuses were added)
        Schema::table('pannes', function (Blueprint $table) {
            $table->enum('statut', ['declaree', 'en_cours', 'en_maintenance', 'resolue', 'irrecuperable'])->default('declaree')->change();
            $table->enum('niveau_gravite', ['mineure', 'majeure', 'critique'])->default('mineure')->change();
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->enum('statut', ['planifiee', 'en_cours', 'terminee'])->default('planifiee')->change();
        });

        Schema::table('affectations', function (Blueprint $table) {
            $table->enum('statut', ['active', 'retournee', 'expiree'])->default('active')->change();
        });
    }
};
