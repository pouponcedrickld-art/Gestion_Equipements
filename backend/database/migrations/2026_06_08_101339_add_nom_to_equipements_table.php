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
        $table->string('nom')->after('id');
    });
}

public function down(): void
{
    Schema::table('equipements', function (Blueprint $table) {
        $table->dropColumn('nom');
    });
}
};
