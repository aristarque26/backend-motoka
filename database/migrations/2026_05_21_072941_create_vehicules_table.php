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
        Schema::create('vehicules', function (Blueprint $table) {
            $table->id('Idvehicule');
            $table->string('immatriculation', 50)->unique();
            $table->enum('TypeVehicule', ['bus', 'taxi', 'camion', 'moto']);
            $table->string('marque', 50);
            $table->string('modele', 50)->nullable();
            $table->string('couleur', 50)->nullable();
            $table->dateTime('annee')->nullable();
            $table->integer('Capacite');
            $table->enum('statut_enum', ['disponible', 'en_mission', 'maintenance', 'hors_service'])->default('disponible');
            $table->dateTime('Date_Expir_Assurance');
            $table->dateTime('visiteTech');
            $table->integer('kilometrage');
            $table->foreignId('Idagence')->constrained('agences', 'Idagence');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicules');
    }
};
