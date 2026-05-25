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
        Schema::create('agences', function (Blueprint $table) {
            $table->id('Idagence');
            $table->string('nom', 50);
            $table->string('slug', 50)->unique();
            $table->string('email', 50)->unique();
            $table->string('telephone', 50);
            $table->string('adresse', 50)->nullable();
            $table->string('logo_url', 50)->nullable();
            $table->enum('plan_enum', ['starter', 'business', 'enterprise'])->default('starter');
            $table->enum('statut_enum', ['actif', 'suspendu', 'ferme'])->default('actif');
            $table->dateTime('ExpirationDate')->nullable();
            $table->string('password', 50);
            $table->foreignId('IdAbonnement')->nullable()->constrained('abonnements', 'IdAbonnement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};
