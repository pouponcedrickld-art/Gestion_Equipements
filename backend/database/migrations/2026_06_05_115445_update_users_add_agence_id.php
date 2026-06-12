<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('agence_id')->nullable()->after('id')->constrained('agences');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $foreignKeyExists = function ($tableName, $column) {
                $constraintName = $tableName . '_' . $column . '_foreign';
                $result = \DB::select(
                    "SELECT COUNT(*) as count FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE CONSTRAINT_SCHEMA = DATABASE() 
                    AND TABLE_NAME = ? 
                    AND CONSTRAINT_NAME = ?",
                    [$tableName, $constraintName]
                );
                return $result[0]->count > 0;
            };
            
            if (Schema::hasColumn('users', 'agence_id') && $foreignKeyExists('users', 'agence_id')) {
                $table->dropForeign(['agence_id']);
            }
            
            if (Schema::hasColumn('users', 'agence_id')) {
                $table->dropColumn('agence_id');
            }
        });
    }
};
