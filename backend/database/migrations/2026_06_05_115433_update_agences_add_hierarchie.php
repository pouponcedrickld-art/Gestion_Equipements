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
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['responsable_id']);
            $table->dropForeign(['gestionnaire_stock_id']);
            $table->dropColumn(['type', 'parent_id', 'ville', 'code_postal', 'telephone', 'email', 'responsable_id', 'gestionnaire_stock_id', 'statut']);
        });
    }
};
