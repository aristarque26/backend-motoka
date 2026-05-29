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
        Schema::table('vehicules', function (Blueprint $table) {
            $table->enum('proprietaire_type', ['agence', 'chauffeur'])->default('agence')->after('statut_enum');
            $table->foreignId('Idchauffeur')->nullable()->after('proprietaire_type')->constrained('chauffeurs', 'Idchauffeur')->onDelete('set null');
        });

        Schema::table('chauffeurs', function (Blueprint $table) {
            $table->enum('type_contrat', ['salarie', 'adherent'])->default('salarie')->after('statut_Enum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->dropForeign(['Idchauffeur']);
            $table->dropColumn(['proprietaire_type', 'Idchauffeur']);
        });

        Schema::table('chauffeurs', function (Blueprint $table) {
            $table->dropColumn('type_contrat');
        });
    }
};
