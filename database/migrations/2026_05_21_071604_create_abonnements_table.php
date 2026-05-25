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
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id('IdAbonnement');
            $table->string('nomAgence', 50);
            $table->string('slug', 50)->unique();
            $table->string('description', 50);
            $table->decimal('prix_mensuel', 15, 2);
            $table->decimal('prix_annuel', 15, 2);
            $table->string('devise', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};
