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
        if (!Schema::hasColumn('equipements', 'quantite')) {
            Schema::table('equipements', function (Blueprint $table) {
                $table->integer('quantite')->default(1)->after('nom');
            });
        }
        
        if (!Schema::hasColumn('equipements', 'is_lot')) {
            Schema::table('equipements', function (Blueprint $table) {
                $table->boolean('is_lot')->default(false)->after('nom');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->dropColumn(['is_lot']);
        });
    }
};
