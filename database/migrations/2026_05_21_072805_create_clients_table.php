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
        Schema::create('clients', function (Blueprint $table) {
            $table->id('Idclient');
            $table->string('nomClient', 50);
            $table->string('emailClient', 50)->nullable();
            $table->string('telephoneClient', 50);
            $table->dateTime('DateInscription');
            $table->string('ville', 50)->nullable();
            $table->string('addresseClient', 50)->nullable();
            $table->enum('typeClient_ENUM', ['particulier', 'entreprise', 'revendeur'])->default('particulier');
            $table->foreignId('Idutilisateur')->constrained('users', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
