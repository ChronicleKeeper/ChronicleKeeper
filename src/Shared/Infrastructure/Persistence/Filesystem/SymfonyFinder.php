<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder as FinderContract;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

use function is_dir;

class SymfonyFinder implements FinderContract
{
    /** @return Finder<SplFileInfo> */
    public function findFilesInDirectory(string $directory, bool $withDotFiles = true): iterable
    {
        if (! is_dir($directory)) {
            return [];
        }

        return (new Finder())
            ->ignoreDotFiles($withDotFiles)
            ->in($directory)
            ->files();
    }

    /** @return iterable<SplFileInfo> */
    public function findFilesInDirectoryOrderedByAccessTimestamp(string $directory, bool $withDotFiles = true): iterable
    {
        if (! is_dir($directory)) {
            return [];
        }

        return (new Finder())
            ->ignoreDotFiles($withDotFiles)
            ->in($directory)
            ->sortByAccessedTime()
            ->reverseSorting()
            ->files();
    }
}
