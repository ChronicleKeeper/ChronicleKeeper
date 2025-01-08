<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\Database\DataProvider;

use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultDataProvider;

final class RootDirectoryProvider extends DefaultDataProvider
{
    public function loadData(DatabasePlatform $platform): void
    {
        $root = RootDirectory::get();

        $platform->insert('directories', [
            'id' => $root->getId(),
            'title' => $root->getTitle(),
            'parent' => null,
        ]);
    }
}
