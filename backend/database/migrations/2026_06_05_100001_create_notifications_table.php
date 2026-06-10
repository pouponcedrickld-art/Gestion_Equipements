<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('type', ['panne', 'retard', 'garantie', 'maintenance', 'affectation', 'perte', 'transfert', 'demande_materiel']);
            $table->string('titre');
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('lu')->default(false);
            $table->enum('canal', ['email', 'sms', 'in_app'])->default('in_app');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
