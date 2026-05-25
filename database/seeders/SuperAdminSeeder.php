<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@motoka.com',
            'password' => Hash::make('motoka1234'),
            'telephone' => '977356358',
            'role_enum' => 'superAdmin',
        ]);
    }
}