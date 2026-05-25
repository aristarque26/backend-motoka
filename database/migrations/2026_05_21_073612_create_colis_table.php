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
        Schema::create('colis', function (Blueprint $table) {
            $table->id('Idcolis');
            $table->string('nomExpediteur', 50);
            $table->string('TelephoneExpedit', 50);
            $table->string('nomDestinateur', 50);
            $table->string('CodeColis', 50)->unique();
            $table->string('Qr_code_Url', 50)->nullable();
            $table->dateTime('Otp_genere')->nullable();
            $table->string('Otp_valide', 50)->nullable();
            $table->enum('statut_enum', ['enregistre', 'en_transit', 'livre', 'recupere'])->default('enregistre');
            $table->string('Signature_Url', 50)->nullable();
            $table->string('Description', 50)->nullable();
            $table->decimal('Poids', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colis');
    }
};
