<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pannes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipement_id')->constrained('equipements');
            $table->foreignId('agent_id')->constrained('agents');
            $table->dateTime('date_declaration');
            $table->text('description');
            $table->enum('niveau_gravite', ['mineure', 'majeure', 'critique'])->default('mineure');
            $table->json('photos')->nullable();
            $table->enum('statut', ['declaree', 'en_cours', 'en_maintenance', 'resolue', 'irrecuperable'])->default('declaree');
            $table->dateTime('date_resolution')->nullable();
            $table->text('solution')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pannes');
    }
};
