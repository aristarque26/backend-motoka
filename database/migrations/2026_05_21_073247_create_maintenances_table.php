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
        Schema::create('maintenances', function (Blueprint $table) {
             $table->id('IdMaintenance');
            $table->dateTime('Date_maintenance');
            $table->integer('Kilometrage');
            $table->enum('Type_Enum', ['vidange', 'pneus', 'freins', 'moteur', 'electrique', 'autres']);
            $table->decimal('CoutMaintenance', 15, 2);
            $table->string('Description', 50)->nullable();
            $table->string('Facture_url', 50)->nullable();
            $table->foreignId('Idvehicule')->constrained('vehicules', 'Idvehicule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
