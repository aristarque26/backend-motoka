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
        Schema::create('succursales', function (Blueprint $table) {
            $table->id('Idsuccursale');
            $table->foreignId('Idagence')->constrained('agences', 'Idagence')->onDelete('cascade');
            $table->string('nom', 100);
            $table->string('ville', 100);
            $table->string('adresse', 255);
            $table->string('telephone', 50);
            $table->foreignId('Idmanager')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('succursales');
    }
};
