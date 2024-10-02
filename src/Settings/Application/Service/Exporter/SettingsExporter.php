<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ZipArchive;

use const DIRECTORY_SEPARATOR;

final readonly class SettingsExporter implements SingleExport
{
    public function __construct(
        private FileAccess $fileAccess,
        private PathRegistry $pathRegistry,
        private SettingsHandler $settingsHandler,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        if (! $this->fileAccess->exists('storage', 'settings.json')) {
            // Create the settings file if it does not exist at this time ... it is not initially created with defaults
            $this->settingsHandler->store();
        }

        $archive->addFile(
            $this->pathRegistry->get('storage') . DIRECTORY_SEPARATOR . 'settings.json',
            'settings.json',
        );
    }
}
