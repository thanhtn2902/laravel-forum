<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    private static Collection $fixtures;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = str(fake()->sentence())->beforeLast('.')->title();

        return [
            'user_id' => User::factory(),
            'title'   => $title,
            'slug'    => Str::slug($title),
            'body'    => Collection::times(4, fn () => fake()->realText(1000))->join(PHP_EOL . PHP_EOL),
         ];
    }

    public function withFixtures(): static
    {
        $posts = static::getFixtures()
            ->map(fn (string $contents) => str($contents)->explode("\n", 2))
            ->map(fn (Collection $part) => [
                'title' => str($part[0])->trim()->after('# '),
                'body' => str($part[1])->trim()
            ]);

        return $this->sequence(...$posts);
    }

    private static function getFixtures(): Collection
    {
        return self::$fixtures ??= collect(File::files(database_path('factories/fixtures/posts')))
            ->map(fn (SplFileInfo $fileInfo) => $fileInfo->getContents());
    }
}
