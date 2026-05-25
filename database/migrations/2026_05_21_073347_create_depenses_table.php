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
        Schema::create('depenses', function (Blueprint $table) {
            $table->id('IdDepense');
            $table->string('Libelle', 50);
            $table->decimal('Montant', 15, 2);
            $table->enum('typeDepense_ENUM', ['carburant', 'salaire', 'maintenance', 'assurance', 'taxe', 'autre']);
            $table->dateTime('Date_Depense');
            $table->string('justificatif_url', 50)->nullable();
            $table->foreignId('Idagence')->constrained('agences', 'Idagence');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
