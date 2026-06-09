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
            $table->dropForeign(['responsable_id']);
            $table->dropColumn(['responsable_id', 'documents']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['code', 'parent_id', 'frequence_maintenance', 'duree_vie', 'attributs_personnalises']);
        });

        Schema::table('consommables', function (Blueprint $table) {
            $table->dropColumn('seuil_alerte');
        });
    }
};
