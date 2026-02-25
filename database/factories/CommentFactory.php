<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use RuntimeException;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-1 month', 'now');
        $userId = User::query()->inRandomOrder()->value('id');

        if (! $userId) {
            throw new RuntimeException('No users found. Create users first before generating comments.');
        }

        return [
            'user_id' => $userId,
            'commentable_type' => Post::class,
            'commentable_id' => Post::factory(),
            'comment' => fake()->paragraph(),
            'created_at' => $createdAt,
            'updated_at' => fake()->dateTimeBetween($createdAt, 'now'),
        ];
    }
}
