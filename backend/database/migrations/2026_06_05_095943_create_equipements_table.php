<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('reference')->unique();
            $table->string('numero_serie')->unique();
            $table->string('imei')->nullable();
            $table->string('code_inventaire')->unique();
            $table->string('marque')->nullable();
            $table->string('modele')->nullable();
            $table->foreignId('categorie_id')->constrained('categories');
            $table->string('fournisseur')->nullable();
            $table->date('date_acquisition')->nullable();
            $table->decimal('prix_achat', 10, 2)->nullable();
            $table->date('garantie_date_fin')->nullable();
            $table->enum('etat', ['neuf', 'en_service', 'en_panne', 'en_maintenance', 'reforme', 'perdu'])->default('neuf');
            $table->string('localisation')->nullable();
            $table->string('photo')->nullable();
            $table->string('qr_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};
