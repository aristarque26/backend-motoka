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
        Schema::create('tracking_gps', function (Blueprint $table) {
            $table->id('IdTracking');
            $table->decimal('Latitude', 15, 2);
            $table->decimal('Longitude', 15, 2);
            $table->decimal('Vitesse', 15, 2);
            $table->integer('Position_Km');
            $table->decimal('altitude', 15, 2);
            $table->string('precisionGPS', 50);
            $table->decimal('angle', 15, 2);
            $table->foreignId('Idvehicule')->constrained('vehicules', 'Idvehicule');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_gps');
    }
};
