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
        Schema::create('itineraries', function (Blueprint $table) {
            $table->id('Iditinerary');
            $table->string('nom');
            $table->string('adresse_depart');
            $table->decimal('latitude_depart', 15, 8)->nullable();
            $table->decimal('longitude_depart', 15, 8)->nullable();
            $table->string('adresse_arrivee');
            $table->decimal('latitude_arrivee', 15, 8)->nullable();
            $table->decimal('longitude_arrivee', 15, 8)->nullable();
            $table->decimal('distance_km_estimee', 15, 2)->nullable();
            $table->decimal('prix_estime', 15, 2)->nullable();
            $table->foreignId('Idagence')->constrained('agences', 'Idagence')->onDelete('cascade');
            $table->foreignId('Idsuccursale')->nullable()->constrained('succursales', 'Idsuccursale')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itineraries');
    }
};
