<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Mettre à jour les lignes existantes (photo NULL → default-avatar.png)
        DB::table('users')->whereNull('photo')->update(['photo' => 'default-avatar.png']);

        // 2. Mettre à jour les prenom NULL → chaîne vide
        DB::table('users')->whereNull('prenom')->update(['prenom' => '']);

        // 3. Mettre à jour les telephone NULL → valeur temporaire (si existant)
        DB::table('users')->whereNull('telephone')->update(['telephone' => '00000000']);

        // 4. Modifier telephone (obligatoire)
        DB::statement('ALTER TABLE users MODIFY telephone VARCHAR(20) NOT NULL');

        // 5. Modifier role_enum (enlever 'client', défaut 'chauffeur')
        DB::statement("ALTER TABLE users MODIFY role_enum ENUM('superAdmin', 'adminAgence', 'dispatcher', 'chauffeur') NOT NULL DEFAULT 'chauffeur'");

        // 6. Modifier photo (défaut 'default-avatar.png')
        DB::statement("ALTER TABLE users MODIFY photo VARCHAR(255) NOT NULL DEFAULT 'default-avatar.png'");

        // 7. Modifier prenom (défaut chaîne vide)
        DB::statement('ALTER TABLE users MODIFY prenom VARCHAR(100) NOT NULL DEFAULT ""');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY telephone VARCHAR(20) NULL');
        DB::statement("ALTER TABLE users MODIFY role_enum ENUM('superAdmin', 'adminAgence', 'dispatcher', 'chauffeur', 'client') NOT NULL DEFAULT 'client'");
        DB::statement('ALTER TABLE users MODIFY photo VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY prenom VARCHAR(100) NULL');
    }
};