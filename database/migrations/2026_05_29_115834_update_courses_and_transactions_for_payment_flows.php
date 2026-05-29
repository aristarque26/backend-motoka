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
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('frais_fret', 15, 2)->default(0)->after('PrixReel');
            $table->decimal('montant_chauffeur', 15, 2)->default(0)->after('frais_fret');
            $table->decimal('montant_agence', 15, 2)->default(0)->after('montant_chauffeur');
            $table->enum('paye_a', ['chauffeur', 'agence'])->default('agence')->after('montant_agence');
        });

        Schema::table('transactions_finances', function (Blueprint $table) {
            $table->string('devise', 3)->default('CDF')->after('montant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['frais_fret', 'montant_chauffeur', 'montant_agence', 'paye_a']);
        });

        Schema::table('transactions_finances', function (Blueprint $table) {
            $table->dropColumn('devise');
        });
    }
};
