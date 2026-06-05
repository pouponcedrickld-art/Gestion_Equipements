<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipement_id')->constrained('equipements');
            $table->foreignId('agent_id')->constrained('agents');
            $table->enum('type', ['perte', 'vol', 'casse'])->default('perte');
            $table->dateTime('date_declaration');
            $table->text('description')->nullable();
            $table->enum('statut', ['declaree', 'validee', 'cloturee'])->default('declaree');
            $table->foreignId('valide_par')->nullable()->constrained('users');
            $table->dateTime('date_validation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertes');
    }
};
