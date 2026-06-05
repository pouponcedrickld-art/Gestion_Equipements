<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consommables', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('type')->nullable();
            $table->foreignId('equipement_id')->constrained('equipements');
            $table->integer('quantite')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consommables');
    }
};
