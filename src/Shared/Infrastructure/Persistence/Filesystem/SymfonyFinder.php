<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder as FinderContract;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class SymfonyFinder implements FinderContract
{
    /** @return Finder<SplFileInfo> */
    public function findFilesInDirectory(string $directory, bool $withDotFiles = true): iterable
    {
        return (new Finder())
            ->ignoreDotFiles($withDotFiles)
            ->in($directory)
            ->files();
    }

    /** @return Finder<SplFileInfo> */
    public function findFilesInDirectoryOrderedByAccessTimestamp(string $directory, bool $withDotFiles = true): iterable
    {
        return (new Finder())
            ->ignoreDotFiles($withDotFiles)
            ->in($directory)
            ->sortByAccessedTime()
            ->reverseSorting()
            ->files();
    }
}
