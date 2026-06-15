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
        // Mise à jour de la table equipements
        Schema::table('equipements', function (Blueprint $table) {
            $table->foreignId('responsable_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('documents')->nullable();
        });

        // Mise à jour de la table categories
        Schema::table('categories', function (Blueprint $table) {
            $table->string('code')->nullable()->unique();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->integer('frequence_maintenance')->nullable()->comment('En mois');
            $table->integer('duree_vie')->nullable()->comment('En années');
            $table->json('attributs_personnalises')->nullable();
        });

        // Mise à jour de la table consommables
        Schema::table('consommables', function (Blueprint $table) {
            $table->integer('seuil_alerte')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
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
            
            if (Schema::hasColumn('equipements', 'responsable_id') && $foreignKeyExists('equipements', 'responsable_id')) {
                $table->dropForeign(['responsable_id']);
            }
            
            $columnsToDrop = ['responsable_id', 'documents'];
            $existingColumns = [];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('equipements', $column)) {
                    $existingColumns[] = $column;
                }
            }
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });

        Schema::table('categories', function (Blueprint $table) {
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
            
            if (Schema::hasColumn('categories', 'parent_id') && $foreignKeyExists('categories', 'parent_id')) {
                $table->dropForeign(['parent_id']);
            }
            
            $columnsToDrop = ['code', 'parent_id', 'frequence_maintenance', 'duree_vie', 'attributs_personnalises'];
            $existingColumns = [];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $existingColumns[] = $column;
                }
            }
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });

        Schema::table('consommables', function (Blueprint $table) {
            if (Schema::hasColumn('consommables', 'seuil_alerte')) {
                $table->dropColumn('seuil_alerte');
            }
        });
    }
};
