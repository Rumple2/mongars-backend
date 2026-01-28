<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Crée ou met à jour l'utilisateur admin principal.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@weetoo.app',
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'WeeToo Admin',
                'username' => 'admin',
                'phone' => null,
                'password' => Hash::make('admin123'),
                'avatar_url' => null,
                'date_of_birth' => null,
                'status' => 'SINGLE',
                'auth_method' => 'EMAIL',
                'is_verified' => true,
                'is_premium' => true,
                'couple_id' => null,
                'last_seen_at' => now(),
            ]
        );
    }
}


