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
        Schema::table('transferts', function (Blueprint $table) {
            $table->foreignId('demande_materiel_id')->nullable()->after('id')->constrained('demandes_materiel')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transferts', function (Blueprint $table) {
            $table->dropForeign(['demande_materiel_id']);
            $table->dropColumn('demande_materiel_id');
        });
    }
};
