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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('IdNotification');
            $table->string('titre', 50);
            $table->string('message', 50);
            $table->enum('canal_ENUM', ['sms', 'whatsapp', 'email', 'push']);
            $table->string('destinataire', 50);
            $table->enum('statutNotification_ENUM', ['envoye', 'echoue', 'en_attente']);
            $table->foreignId('Idagence')->constrained('agences', 'Idagence');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
