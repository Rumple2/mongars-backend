<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AdminUserSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder principal pour l'utilisateur admin
        $this->call(AdminUserSeeder::class);

        // Exemple : autres seeders / données de test
        // User::factory(10)->create();
    }
}
