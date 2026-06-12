<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->enum('type', ['generale', 'sous_agence'])->default('sous_agence')->after('id');
            $table->foreignId('parent_id')->nullable()->after('type')->constrained('agences');
            $table->string('ville')->nullable()->after('adresse');
            $table->string('code_postal')->nullable()->after('ville');
            $table->string('telephone')->nullable()->after('code_postal');
            $table->string('email')->nullable()->after('telephone');
            $table->foreignId('responsable_id')->nullable()->after('email')->constrained('users');
            $table->foreignId('gestionnaire_stock_id')->nullable()->after('responsable_id')->constrained('users');
            $table->enum('statut', ['active', 'inactive'])->default('active')->after('gestionnaire_stock_id');
        });
    }

    public function down(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            // Fonction pour vérifier l'existence d'une contrainte étrangère
            $foreignKeyExists = function ($column) {
                $constraintName = 'agences_' . $column . '_foreign';
                $result = \DB::select(
                    "SELECT COUNT(*) as count FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE CONSTRAINT_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'agences' 
                    AND CONSTRAINT_NAME = ?",
                    [$constraintName]
                );
                return $result[0]->count > 0;
            };
            
            if (Schema::hasColumn('agences', 'parent_id') && $foreignKeyExists('parent_id')) {
                $table->dropForeign(['parent_id']);
            }
            if (Schema::hasColumn('agences', 'responsable_id') && $foreignKeyExists('responsable_id')) {
                $table->dropForeign(['responsable_id']);
            }
            if (Schema::hasColumn('agences', 'gestionnaire_stock_id') && $foreignKeyExists('gestionnaire_stock_id')) {
                $table->dropForeign(['gestionnaire_stock_id']);
            }
            
            $columnsToDrop = ['type', 'parent_id', 'ville', 'code_postal', 'telephone', 'email', 'responsable_id', 'gestionnaire_stock_id', 'statut'];
            $existingColumns = [];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('agences', $column)) {
                    $existingColumns[] = $column;
                }
            }
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
