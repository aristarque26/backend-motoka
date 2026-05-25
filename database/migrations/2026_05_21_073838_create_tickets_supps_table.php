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
        Schema::create('tickets_supps', function (Blueprint $table) {
            $table->id('IdTicketSupp');
            $table->string('numero_ticket', 50)->unique();
            $table->string('sujet', 50);
            $table->string('message', 50);
            $table->enum('priorite_ENUM', ['basse', 'moyenne', 'haute', 'urgente']);
            $table->enum('statut_ENUM', ['ouvert', 'en_cours', 'resolu', 'ferme']);
            $table->string('categorie', 50);
            $table->string('resolution', 50)->nullable();
            $table->foreignId('Idutilisateur')->constrained('users', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets_supps');
    }
};
