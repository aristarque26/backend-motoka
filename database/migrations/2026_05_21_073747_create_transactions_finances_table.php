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
        Schema::create('transactions_finances', function (Blueprint $table) {
            $table->id('IdTransactionFinance');
            $table->integer('montant');
            $table->enum('mode_paiement_Enum', ['especes', 'carte_bancaire', 'm_pesa', 'orange_money', 'airtel_money']);
            $table->enum('Type_Transaction_Enum', ['course', 'colis', 'abonnements', 'autre']);
            $table->dateTime('Date_Paiement');
            $table->foreignId('Idcource')->constrained('courses', 'Idcource');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions_finances');
    }
};
