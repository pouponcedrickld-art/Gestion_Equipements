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
        Schema::table('equipements', function (Blueprint $table) {
            $table->string('etat')->change();
            $table->string('statut_global')->change();
            $table->string('numero_serie')->nullable()->change();
            $table->string('reference')->nullable()->change();
            $table->string('code_inventaire')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->enum('etat', ['neuf', 'en_service', 'en_panne', 'en_maintenance', 'reforme', 'perdu'])->default('neuf')->change();
            $table->enum('statut_global', ['en_stock_general', 'en_transit', 'en_stock_local', 'affecte', 'en_panne', 'en_maintenance', 'reforme'])->default('en_stock_general')->change();
            $table->string('numero_serie')->nullable(false)->change();
            $table->string('reference')->nullable(false)->change();
            $table->string('code_inventaire')->nullable(false)->change();
        });
    }
};
