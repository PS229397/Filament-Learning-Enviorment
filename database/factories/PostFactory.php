<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(fake()->numberBetween(3, 6));
        $createdAt = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'thumbnail' => fake()->optional()->imageUrl(1200, 630, 'tech', true),
            'title' => $title,
            'color' => fake()->hexColor(),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'category_id' => Category::query()->inRandomOrder()->value('id') ?? Category::factory(),
            'content' => fake()->paragraphs(fake()->numberBetween(2, 6), true),
            'published' => fake()->boolean(80),
            'tags' => fake()->words(fake()->numberBetween(1, 4)),
            'created_at' => $createdAt,
            'updated_at' => fake()->dateTimeBetween($createdAt, 'now'),
        ];
    }
}
