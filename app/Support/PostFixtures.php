<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Support\Facades\File;

class PostFixtures
{
    public static function getFixtures(): Collection
    {
        return once(fn () => collect(File::files(database_path('factories/fixtures/posts')))
            ->map(fn (SplFileInfo $fileInfo) => $fileInfo->getContents())
            ->map(fn (string $contents) => str($contents)->explode("\n", 2))
            ->map(fn (Collection $part) => [
                'title' => str($part[0])->trim()->after('# '),
                'body' => str($part[1])->trim()
            ]));
    }
}
