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
        // En SQLite, on ne peut pas modifier une colonne enum facilement.
        // Mais Laravel peut le faire si doctrine/dbal est installé, ou en recréant la table.
        // Alternativement, on peut juste ignorer le type enum strict en SQLite si on veut.
        // Pour ce projet, on va essayer de changer le type.
        
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_enum')->change(); // Passer en string pour plus de flexibilité
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role_enum', ['superAdmin', 'adminAgence', 'dispatcher', 'chauffeur', 'client'])->change();
        });
    }
};
