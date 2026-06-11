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
        Schema::table('transactions_finances', function (Blueprint $table) {
            $table->foreignId('Idcource')->nullable()->change();
            $table->string('devise', 5)->default('CDF')->after('montant');
            $table->foreignId('Idagence')->nullable()->after('Idcource')->constrained('agences', 'Idagence')->onDelete('cascade');
            $table->foreignId('Idchauffeur')->nullable()->after('Idagence')->constrained('chauffeurs', 'Idchauffeur')->onDelete('set null');
            $table->foreignId('Idsuccursale')->nullable()->after('Idchauffeur')->constrained('succursales', 'Idsuccursale')->onDelete('set null');
            $table->string('reference_paiement')->nullable()->after('mode_paiement_Enum');
            $table->text('description')->nullable()->after('Type_Transaction_Enum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions_finances', function (Blueprint $table) {
            $table->foreignId('Idcource')->nullable(false)->change();
            $table->dropForeign(['Idagence']);
            $table->dropForeign(['Idchauffeur']);
            $table->dropForeign(['Idsuccursale']);
            $table->dropColumn(['devise', 'Idagence', 'Idchauffeur', 'Idsuccursale', 'reference_paiement', 'description']);
        });
    }
};
