<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technicien_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('type_maintenance', ['preventif', 'corrective'])->default('corrective');
            $table->dateTime('date_prevue')->nullable();
            $table->string('responsable')->nullable();
            $table->string('technicien')->nullable();
            $table->text('diagnostic')->nullable();
            $table->decimal('cout', 10, 2)->nullable();
            $table->dateTime('date_debut')->nullable();
            $table->dateTime('date_fin')->nullable();
            $table->text('observations')->nullable();
            $table->enum('statut', ['planifiee', 'en_cours', 'terminee'])->default('planifiee');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
