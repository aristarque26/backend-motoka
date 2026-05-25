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
        Schema::create('chauffeurs', function (Blueprint $table) {
             $table->id('Idchauffeur');
            $table->string('nomChauffeur', 50);
            $table->string('telephone', 50);
            $table->string('adresse', 50)->nullable();
            $table->string('numeroPermis', 50)->unique();
            $table->string('Url_Permis', 50)->nullable();
            $table->string('photo', 50)->nullable();
            $table->dateTime('DateValidite');
            $table->enum('statut_Enum', ['dispo', 'en_course', 'conge', 'suspendu'])->default('dispo');
            $table->decimal('salaireBase', 15, 2);
            $table->dateTime('DateEmbauche');
            $table->decimal('commission', 15, 2);
            $table->decimal('revenu', 15, 2);
            $table->integer('NbreCourse');
            $table->decimal('NoteMoyenne', 15, 2);
            $table->foreignId('Idutilisateur')->constrained('users', 'id');
            $table->foreignId('Idagence')->constrained('agences', 'Idagence');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chauffeurs');
    }
};
