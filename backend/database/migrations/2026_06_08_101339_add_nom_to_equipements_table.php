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
        if (!Schema::hasColumn('equipements', 'nom')) {
            Schema::table('equipements', function (Blueprint $table) {
                $table->string('nom')->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('equipements', 'nom')) {
            Schema::table('equipements', function (Blueprint $table) {
                $table->dropColumn('nom');
            });
        }
    }
};
