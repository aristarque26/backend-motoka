<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            // 1. Modifier TypeVehicule pour ajouter 'minibus'
            $table->enum('TypeVehicule', ['bus', 'taxi', 'camion', 'moto', 'minibus'])->change();
            
            // 2. Ajouter CapacitePassagers (à côté de Capacite)
            $table->integer('CapacitePassagers')->nullable()->after('Capacite');
            
            // 3. Ajouter CapacitePoids
            $table->decimal('CapacitePoids', 10, 2)->nullable()->after('CapacitePassagers');
            
            // 4. Ajouter VolumeBagages
            $table->decimal('VolumeBagages', 10, 2)->nullable()->after('CapacitePoids');
        });
    }

    public function down(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->enum('TypeVehicule', ['bus', 'taxi', 'camion', 'moto'])->change();
            $table->dropColumn(['CapacitePassagers', 'CapacitePoids', 'VolumeBagages']);
        });
    }
};