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
        $tables = ['users', 'vehicules', 'courses', 'colis', 'depenses', 'transactions_finances'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->foreignId('Idsuccursale')->nullable()->constrained('succursales', 'Idsuccursale')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'vehicules', 'courses', 'colis', 'depenses', 'transactions_finances'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['Idsuccursale']);
                $table->dropColumn('Idsuccursale');
            });
        }
    }
};
