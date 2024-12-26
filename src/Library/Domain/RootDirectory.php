<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain;

use ChronicleKeeper\Library\Domain\Entity\Directory;

final class RootDirectory
{
    public const string ID = 'caf93493-9072-44e2-a6db-4476985a849d';

    public static function get(): Directory
    {
        return new Directory(
            self::ID,
            'Hauptverzeichnis',
            null,
        );
    }
}
