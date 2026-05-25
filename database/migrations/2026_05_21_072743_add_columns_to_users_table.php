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
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone', 20)->nullable()->after('email');
            $table->enum('role_enum', ['superAdmin', 'adminAgence', 'dispatcher', 'chauffeur', 'client'])->default('client')->after('password');
            $table->string('photo', 255)->nullable()->after('role_enum');
            $table->string('prenom', 100)->nullable()->after('name');
            $table->foreignId('Idagence')->nullable()->constrained('agences', 'Idagence')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telephone', 'role_enum', 'photo', 'prenom']);
            $table->dropForeign(['Idagence']);
            $table->dropColumn('Idagence');
        });
    }
};
