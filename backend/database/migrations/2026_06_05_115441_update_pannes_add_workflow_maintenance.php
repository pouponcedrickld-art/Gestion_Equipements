<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pannes', function (Blueprint $table) {
            $table->foreignId('gestionnaire_stock_id')->nullable()->after('agent_id')->constrained('users');
            $table->foreignId('technicien_id')->nullable()->after('gestionnaire_stock_id')->constrained('users');
            $table->text('diagnostic_technicien')->nullable()->after('photos');
            $table->text('action_realisee')->nullable()->after('diagnostic_technicien');
            $table->decimal('cout_reparation', 10, 2)->nullable()->after('action_realisee');
            $table->enum('decision_finale', ['repare', 'irrecuperable', 'remplacement', 'en_attente'])->default('en_attente')->after('date_resolution');
        });
    }

    public function down(): void
    {
        Schema::table('pannes', function (Blueprint $table) {
            $table->dropForeign(['gestionnaire_stock_id']);
            $table->dropForeign(['technicien_id']);
            $table->dropColumn(['gestionnaire_stock_id', 'technicien_id', 'diagnostic_technicien', 'action_realisee', 'cout_reparation', 'decision_finale']);
        });
    }
};
