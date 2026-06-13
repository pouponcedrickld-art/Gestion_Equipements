<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type')->change(); // Drop enum constraint, use string for flexibility
        });
    }

    public function down(): void
    {
        // If needed, revert to original enum
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('type', ['panne', 'retard', 'garantie', 'maintenance', 'affectation', 'perte', 'transfert', 'demande_materiel'])->change();
        });
    }
};
