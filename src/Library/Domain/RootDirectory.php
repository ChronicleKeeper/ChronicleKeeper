<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ReflectionClass;

final class RootDirectory
{
    public const string ID = 'caf93493-9072-44e2-a6db-4476985a849d';

    public static function get(): Directory
    {
        // Workaround because the constructor of the directory is instantiating the root directory
        $directory = (new ReflectionClass(Directory::class))->newInstanceWithoutConstructor();

        $directory->title  = 'Hauptverzeichnis';
        $directory->id     = self::ID;
        $directory->parent = null; // Only root is allowed to have null

        return $directory;
    }
}
