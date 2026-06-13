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
        Schema::table('abonnements', function (Blueprint $table) {
            $table->integer('max_succursales')->default(3);
            $table->integer('max_vehicules')->default(5);
            $table->integer('max_utilisateurs')->default(2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            $table->dropColumn(['max_succursales', 'max_vehicules', 'max_utilisateurs']);
        });
    }
};
