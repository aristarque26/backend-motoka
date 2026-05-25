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
        Schema::create('alertes_maintenance', function (Blueprint $table) {
            $table->id('IdAlertemaintenance');
            $table->string('type_alerte', 50);
            $table->integer('seuil_valeur');
            $table->string('message', 50)->nullable();
            $table->dateTime('date_alerte');
            $table->foreignId('Idvehicule')->constrained('vehicules', 'Idvehicule');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertes_maintenance');
    }
};
