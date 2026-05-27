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

        // Utiliser le Schema Builder de Laravel pour la portabilité (notamment SQLite)
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone', 20)->nullable(false)->change();
            // Utiliser string pour role_enum pour la compatibilité maximale SQLite/MySQL
            $table->string('role_enum')->default('chauffeur')->change();
            $table->string('photo', 255)->default('default-avatar.png')->nullable(false)->change();
            $table->string('prenom', 100)->default('')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone', 20)->nullable()->change();
            $table->string('role_enum')->default('client')->change();
            $table->string('photo', 255)->nullable()->change();
            $table->string('prenom', 100)->nullable()->change();
        });
    }
};