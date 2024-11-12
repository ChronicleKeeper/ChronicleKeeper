<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Service\Exporter;

use ChronicleKeeper\Settings\Application\Service\Exporter\SingleExport;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ZipArchive;

use const DIRECTORY_SEPARATOR;

final readonly class FavoritesExport implements SingleExport
{
    public function __construct(
        private PathRegistry $pathRegistry,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $archive->addFile(
            $this->pathRegistry->get('storage') . DIRECTORY_SEPARATOR . 'favorites.json',
            'favorites.json',
        );
    }
}
