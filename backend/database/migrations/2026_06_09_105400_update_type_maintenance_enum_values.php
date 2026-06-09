<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE maintenances MODIFY COLUMN type_maintenance ENUM('préventif', 'correctif') DEFAULT 'correctif'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE maintenances MODIFY COLUMN type_maintenance ENUM('preventive', 'corrective') DEFAULT 'corrective'");
    }
};