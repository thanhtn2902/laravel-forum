<?php

namespace Database\Factories;

use App\Models\Topic;
use App\Models\User;
use App\Support\PostFixtures;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
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
        $title = str(fake()->sentence())->beforeLast('.')->title();

        return [
            'user_id'     => User::factory(),
            'topic_id'    => Topic::factory(),
            'title'       => $title,
            'slug'        => Str::slug($title),
            'body'        => Collection::times(4, fn () => fake()->realText(1000))->join(PHP_EOL.PHP_EOL),
            'likes_count' => 0,
        ];
    }

    public function withFixtures(): static
    {
        return $this->sequence(...PostFixtures::getFixtures());
    }
}
