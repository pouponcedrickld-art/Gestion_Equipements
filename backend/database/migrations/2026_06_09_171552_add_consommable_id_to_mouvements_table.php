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
        Schema::table('mouvements', function (Blueprint $table) {
            $table->foreignId('consommable_id')->nullable()->after('equipement_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipement_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mouvements', function (Blueprint $table) {
            $table->dropForeign(['consommable_id']);
            $table->dropColumn('consommable_id');
            $table->foreignId('equipement_id')->nullable(false)->change();
        });
    }
};
