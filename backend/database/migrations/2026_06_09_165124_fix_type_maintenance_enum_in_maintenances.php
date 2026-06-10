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
        // Modifier l'enum pour utiliser les valeurs françaises
        DB::statement("ALTER TABLE maintenances MODIFY type_maintenance ENUM('préventif', 'correctif') DEFAULT 'correctif'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir aux valeurs anglaises
        DB::statement("ALTER TABLE maintenances MODIFY type_maintenance ENUM('preventif', 'corrective') DEFAULT 'corrective'");
    }
};
