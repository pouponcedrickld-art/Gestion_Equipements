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
        Schema::table('agences', function (Blueprint $table) {
            if (!Schema::hasColumn('agences', 'code_postal')) {
                $table->string('code_postal')->nullable();
            }
            if (!Schema::hasColumn('agences', 'telephone')) {
                $table->string('telephone')->nullable();
            }
            if (!Schema::hasColumn('agences', 'email')) {
                $table->string('email')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->dropColumn(['code_postal', 'telephone', 'email']);
        });
    }
};
