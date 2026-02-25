<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use RuntimeException;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categoryIds = Category::query()->pluck('id');

        if ($categoryIds->isEmpty()) {
            throw new RuntimeException('No categories found. Create categories first before running PostSeeder.');
        }

        if (User::query()->count() === 0) {
            User::factory()->count(10)->create();
        }

        $users = User::query()->pluck('id');
        $authorUsers = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_EDITOR])
            ->pluck('id');

        Post::factory()
            ->count(80)
            ->state(fn () => ['category_id' => $categoryIds->random()])
            ->create()
            ->each(function (Post $post) use ($users, $authorUsers): void {
                $maxAuthors = min(3, $authorUsers->count());
                $authorIds = $authorUsers
                    ->shuffle()
                    ->take($maxAuthors > 0 ? fake()->numberBetween(1, $maxAuthors) : 0);

                $authorPayload = $authorIds
                    ->values()
                    ->mapWithKeys(fn ($userId, $index) => [
                        (int) $userId => [
                            'order' => $index + 1,
                            'created_at' => $post->created_at,
                            'updated_at' => $post->updated_at,
                        ],
                    ])
                    ->all();

                $post->authors()->syncWithoutDetaching($authorPayload);

                $commentsCount = fake()->numberBetween(0, 8);

                if ($commentsCount === 0) {
                    return;
                }

                Comment::factory()
                    ->count($commentsCount)
                    ->state(fn () => [
                        'user_id' => $users->random(),
                        'commentable_type' => Post::class,
                        'commentable_id' => $post->id,
                    ])
                    ->create();
            });
    }
}
