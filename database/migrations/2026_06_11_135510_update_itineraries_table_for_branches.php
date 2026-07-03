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
        Schema::table('itineraries', function (Blueprint $table) {
            $table->foreignId('Idsuccursale_depart')->nullable()->after('Idsuccursale')->constrained('succursales', 'Idsuccursale')->onDelete('set null');
            $table->foreignId('Idsuccursale_arrivee')->nullable()->after('Idsuccursale_depart')->constrained('succursales', 'Idsuccursale')->onDelete('set null');
            $table->decimal('prix_base_passager', 15, 2)->nullable()->after('prix_estime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            $table->dropForeign(['Idsuccursale_depart']);
            $table->dropForeign(['Idsuccursale_arrivee']);
            $table->dropColumn(['Idsuccursale_depart', 'Idsuccursale_arrivee', 'prix_base_passager']);
        });
    }
};
