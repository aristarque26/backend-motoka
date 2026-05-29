<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateUsersColumnsDefaultValues extends Migration
{
    public function up(): void
    {
        // Mise à jour des valeurs NULL
        DB::table('users')->whereNull('photo')->update(['photo' => 'default-avatar.png']);
        DB::table('users')->whereNull('prenom')->update(['prenom' => '']);
        DB::table('users')->whereNull('telephone')->update(['telephone' => '00000000']);

        if (DB::getDriverName() === 'mysql') {
            // ✅ Syntaxe MySQL
            DB::statement('ALTER TABLE users MODIFY telephone VARCHAR(20) NOT NULL');
            DB::statement("ALTER TABLE users MODIFY role_enum ENUM('superAdmin','adminAgence','dispatcher','chauffeur') NOT NULL DEFAULT 'chauffeur'");
            DB::statement("ALTER TABLE users MODIFY photo VARCHAR(255) NOT NULL DEFAULT 'default-avatar.png'");
            DB::statement('ALTER TABLE users MODIFY prenom VARCHAR(100) NOT NULL DEFAULT ""');
        } elseif (DB::getDriverName() === 'sqlite') {
            // ✅ Syntaxe SQLite (pas de MODIFY, on utilise Schema::table + change())
            Schema::table('users', function (Blueprint $table) {
                $table->string('telephone', 20)->default('00000000')->nullable(false)->change();
                $table->string('role_enum')->default('chauffeur')->change();
                $table->string('photo')->default('default-avatar.png')->change();
                $table->string('prenom', 100)->default('')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY telephone VARCHAR(20) NULL');
            DB::statement("ALTER TABLE users MODIFY role_enum ENUM('superAdmin','adminAgence','dispatcher','chauffeur','client') NOT NULL DEFAULT 'client'");
            DB::statement('ALTER TABLE users MODIFY photo VARCHAR(255) NULL');
            DB::statement('ALTER TABLE users MODIFY prenom VARCHAR(100) NULL');
        } elseif (DB::getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->string('telephone', 20)->nullable()->change();
                $table->string('role_enum')->default('client')->change();
                $table->string('photo')->nullable()->change();
                $table->string('prenom', 100)->nullable()->change();
            });
        }
    }
}
