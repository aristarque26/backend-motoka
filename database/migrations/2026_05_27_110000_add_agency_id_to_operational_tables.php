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
        // Ajout de Idagence à la table clients
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'Idagence')) {
                $table->foreignId('Idagence')->nullable()->constrained('agences', 'Idagence')->after('Idclient');
            }
        });

        // Ajout de Idagence et Idvehicule à la table courses
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'Idagence')) {
                $table->foreignId('Idagence')->nullable()->constrained('agences', 'Idagence')->after('Idcource');
            }
            if (!Schema::hasColumn('courses', 'Idvehicule')) {
                $table->foreignId('Idvehicule')->nullable()->constrained('vehicules', 'Idvehicule')->after('Idchauffeur');
            }
        });

        // Ajout de Idagence à la table colis
        Schema::table('colis', function (Blueprint $table) {
            if (!Schema::hasColumn('colis', 'Idagence')) {
                $table->foreignId('Idagence')->nullable()->constrained('agences', 'Idagence')->after('Idcolis');
            }
            if (!Schema::hasColumn('colis', 'Idclient')) {
                $table->foreignId('Idclient')->nullable()->constrained('clients', 'Idclient')->after('Idagence');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['Idagence']);
            $table->dropColumn('Idagence');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['Idagence']);
            $table->dropForeign(['Idvehicule']);
            $table->dropColumn(['Idagence', 'Idvehicule']);
        });

        Schema::table('colis', function (Blueprint $table) {
            $table->dropForeign(['Idagence']);
            $table->dropForeign(['Idclient']);
            $table->dropColumn(['Idagence', 'Idclient']);
        });
    }
};
