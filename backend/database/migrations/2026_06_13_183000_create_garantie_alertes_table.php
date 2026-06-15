<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garantie_alertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipement_id')->constrained()->cascadeOnDelete();
            $table->integer('seuil_jours'); // 90, 60, 30, 7
            $table->timestamp('date_envoi');
            $table->timestamps();

            $table->unique(['equipement_id', 'seuil_jours']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garantie_alertes');
    }
};
