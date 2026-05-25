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
        Schema::create('recoit', function (Blueprint $table) {
            $table->id('IdRecoit');
            $table->foreignId('Idutilisateur')->constrained('users', 'id');
            $table->foreignId('IdNotification')->constrained('notifications', 'IdNotification');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recoit');
    }
};
