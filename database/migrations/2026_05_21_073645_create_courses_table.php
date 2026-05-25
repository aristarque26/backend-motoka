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
        Schema::create('courses', function (Blueprint $table) {
             $table->id('Idcource');
            $table->string('nomCourse', 50);
            $table->string('AdresseDepart', 50);
            $table->decimal('LatitudeDepart', 15, 2);
            $table->decimal('LongitudeDepart', 15, 2);
            $table->string('AdresseArrive', 50);
            $table->decimal('LatitudeArrivee', 15, 2);
            $table->decimal('LongitudeArrive', 15, 2);
            $table->decimal('Distance_Km', 15, 2);
            $table->decimal('PrixEstime', 15, 2);
            $table->decimal('PrixReel', 15, 2);
            $table->enum('statut_enum', ['en_attente', 'en_cours', 'termine', 'annulee'])->default('en_attente');
            $table->foreignId('Idclient')->constrained('clients', 'Idclient');
            $table->foreignId('Idchauffeur')->constrained('chauffeurs', 'Idchauffeur');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
