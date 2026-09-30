<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Exécute le seeder.
     */
    public function run(): void
    {
        if (!User::where('email', 'admin@piaa.com')->exists()) {
            User::create([
                'name' => 'Administrateur',
                'email' => 'admin@piaa.com',
                'password' => Hash::make('adminpiaa123'),
                'type_compte_id' => 1,
            ]);
        }

        if (!User::where('email', 'contentmanager@piaa.com')->exists()) {
            User::create([
                'name' => 'Content Manager',
                'email' => 'contentmanager@piaa.com',
                'password' => Hash::make('managerpiaa123'),
                'type_compte_id' => 2,
            ]);
        }
    }
}
