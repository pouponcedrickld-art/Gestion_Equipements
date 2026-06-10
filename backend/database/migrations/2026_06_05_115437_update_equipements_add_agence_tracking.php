<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->foreignId('agence_proprietaire_id')->nullable()->after('localisation')->constrained('agences');
            $table->foreignId('agence_actuelle_id')->nullable()->after('agence_proprietaire_id')->constrained('agences');
            $table->enum('statut_global', ['en_stock_general', 'en_transit', 'en_stock_local', 'affecte', 'en_panne', 'en_maintenance', 'reforme'])->default('en_stock_general')->after('etat');
        });
    }

    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->dropForeign(['agence_proprietaire_id']);
            $table->dropForeign(['agence_actuelle_id']);
            $table->dropColumn(['agence_proprietaire_id', 'agence_actuelle_id', 'statut_global']);
        });
    }
};
