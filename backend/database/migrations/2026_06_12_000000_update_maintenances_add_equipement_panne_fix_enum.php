<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Add foreign keys for equipement and panne (if not already present)
            if (!Schema::hasColumn('maintenances', 'equipement_id')) {
                $table->foreignId('equipement_id')->nullable()->after('id')->constrained('equipements')->onDelete('cascade');
            }
            if (!Schema::hasColumn('maintenances', 'panne_id')) {
                $table->foreignId('panne_id')->nullable()->after('equipement_id')->constrained('pannes')->onDelete('cascade');
            }

            // Fix type_maintenance enum: change "preventif" to "preventive" to match code
            $table->string('type_maintenance')->change();
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['equipement_id']);
            $table->dropForeign(['panne_id']);
            $table->dropColumn(['equipement_id', 'panne_id']);
            $table->enum('type_maintenance', ['preventif', 'corrective'])->default('corrective')->change();
        });
    }
};
