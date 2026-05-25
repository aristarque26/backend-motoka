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
        Schema::create('salaire_chauffeurs', function (Blueprint $table) {
            $table->id('IdSalaireChauffeur');
            $table->integer('Mois');
            $table->integer('Annee');
            $table->decimal('Montant_base', 15, 2);
            $table->decimal('Commission', 15, 2);
            $table->decimal('Primes', 15, 2);
            $table->decimal('Deduction', 15, 2);
            $table->string('Montant_total', 50);
            $table->enum('Statut_Salaire_ENUM', ['en_attente', 'paye'])->default('en_attente');
            $table->dateTime('Date_paiement')->nullable();
            $table->integer('nbre_courses');
            $table->decimal('revenu_course', 15, 2);
            $table->foreignId('Idchauffeur')->constrained('chauffeurs', 'Idchauffeur');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaire_chauffeurs');
    }
};
