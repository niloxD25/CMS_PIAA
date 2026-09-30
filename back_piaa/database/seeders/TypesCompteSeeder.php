<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesCompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('types_compte')->insert([
            [
                'type' => 'Administrateur',
                'niveau_acces' => 0,
                'deleted_at' => null,
            ],
            [
                'type' => 'Content manager',
                'niveau_acces' => 1,
                'deleted_at' => null,
            ],
            [
                'type' => 'Utilisateur connecté',
                'niveau_acces' => 2,
                'deleted_at' => null,
            ],
            [
                'type' => 'Utilisateur normal',
                'niveau_acces' => 999,
                'deleted_at' => null,
            ],
        ]);
    }
}
