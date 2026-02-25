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
        $adminCreatedAt = fake()->dateTimeBetween('-1 month', 'now');
        User::query()->updateOrCreate(
            ['email' => 'admin@email.com'],
            [
                'name' => 'ADMIN',
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make('12345678'),
                'created_at' => $adminCreatedAt,
                'updated_at' => fake()->dateTimeBetween($adminCreatedAt, 'now'),
            ]
        );

        $editorCreatedAt = fake()->dateTimeBetween('-1 month', 'now');
        User::query()->updateOrCreate(
            ['email' => 'editor@email.com'],
            [
                'name' => 'EDITOR',
                'role' => User::ROLE_EDITOR,
                'password' => Hash::make('12345678'),
                'created_at' => $editorCreatedAt,
                'updated_at' => fake()->dateTimeBetween($editorCreatedAt, 'now'),
            ]
        );

        $userCreatedAt = fake()->dateTimeBetween('-1 month', 'now');
        User::query()->updateOrCreate(
            ['email' => 'user@email.com'],
            [
                'name' => 'USER',
                'role' => User::ROLE_USER,
                'password' => Hash::make('12345678'),
                'created_at' => $userCreatedAt,
                'updated_at' => fake()->dateTimeBetween($userCreatedAt, 'now'),
            ]
        );

        User::factory()->count(25)->create();
    }
}
