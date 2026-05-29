<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@motoka.com'], // condition de recherche
            [
                'name' => 'Super Admin',
                'password' => Hash::make('motoka1234'),
                'telephone' => '977356358',
                'role_enum' => 'superAdmin',
                'est_actif' => true,
            ]
        );
    }
}
