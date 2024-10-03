<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts;

use SplFileInfo;

interface Finder
{
    /** @return \Symfony\Component\Finder\Finder<SplFileInfo> */
    public function findFilesInDirectory(string $directory): iterable;

    /** @return \Symfony\Component\Finder\Finder<SplFileInfo> */
    public function findFilesInDirectoryOrderedByAccessTimestamp(string $directory, bool $withDotFiles = true): iterable;
}
