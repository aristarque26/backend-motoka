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
        Schema::table('colis', function (Blueprint $table) {
            $table->decimal('prix', 15, 2)->nullable()->after('Poids');
            $table->string('devise', 5)->default('CDF')->after('prix');
            $table->enum('methode_calcul_prix', ['poids', 'manuel'])->default('manuel')->after('devise');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colis', function (Blueprint $table) {
            $table->dropColumn(['prix', 'devise', 'methode_calcul_prix']);
        });
    }
};
