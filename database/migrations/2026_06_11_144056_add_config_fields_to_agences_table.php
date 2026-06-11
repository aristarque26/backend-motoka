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
        Schema::table('agences', function (Blueprint $table) {
            if (!Schema::hasColumn('agences', 'config_prix_km')) {
                $table->decimal('config_prix_km', 15, 2)->default(0)->after('email');
            }
            if (!Schema::hasColumn('agences', 'config_frais_adhesion')) {
                $table->decimal('config_frais_adhesion', 15, 2)->default(50)->after('config_prix_km');
            }
            if (!Schema::hasColumn('agences', 'config_commission_defaut')) {
                $table->decimal('config_commission_defaut', 5, 2)->default(10)->after('config_frais_adhesion');
            }
            if (!Schema::hasColumn('agences', 'logo_url')) {
                $table->string('logo_url')->nullable()->after('config_commission_defaut');
            }
            if (!Schema::hasColumn('agences', 'couleur_primaire')) {
                $table->string('couleur_primaire')->default('#3b82f6')->after('logo_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $columns = ['config_prix_km', 'config_frais_adhesion', 'config_commission_defaut', 'logo_url', 'couleur_primaire'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('agences', $col)) {
                    try { $table->dropColumn($col); } catch (\Exception $e) {}
                }
            }
        });
    }
};
