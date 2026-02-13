<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->create([
            'name' => 'ADMIN',
            'email' => 'admin@email.com',
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make('12345678'),
        ]);

        User::query()->create([
            'name' => 'EDITOR',
            'email' => 'editor@email.com',
            'role' => User::ROLE_EDITOR,
            'password' => Hash::make('12345678'),
        ]);

        User::query()->create([
            'name' => 'USER',
            'email' => 'user@email.com',
            'role' => User::ROLE_USER,
            'password' => Hash::make('12345678'),
        ]);
    }
}
