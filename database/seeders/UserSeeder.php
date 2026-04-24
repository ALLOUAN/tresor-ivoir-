<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'uuid' => (string) Str::uuid(),
                'email' => 'admin@tresorsdivoire.ci',
                'password_hash' => Hash::make('Admin@2025!'),
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'role' => 'admin',
                'is_active' => true,
                'is_verified' => true,
                'locale' => 'fr',
                'email_verified_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'email' => 'editeur@tresorsdivoire.ci',
                'password_hash' => Hash::make('Editor@2025!'),
                'first_name' => 'Aminata',
                'last_name' => 'Koné',
                'role' => 'editor',
                'is_active' => true,
                'is_verified' => true,
                'locale' => 'fr',
                'email_verified_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'email' => 'prestataire@example.ci',
                'password_hash' => Hash::make('Provider@2025!'),
                'first_name' => 'Kouassi',
                'last_name' => 'Brou',
                'phone' => '+22507000001',
                'role' => 'provider',
                'is_active' => true,
                'is_verified' => true,
                'locale' => 'fr',
                'email_verified_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'email' => 'visiteur@example.ci',
                'password_hash' => Hash::make('Visitor@2025!'),
                'first_name' => 'Marie',
                'last_name' => 'Diouf',
                'role' => 'visitor',
                'is_active' => true,
                'is_verified' => true,
                'locale' => 'fr',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }
    }
}
