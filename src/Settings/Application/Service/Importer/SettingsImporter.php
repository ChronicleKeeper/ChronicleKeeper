<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use League\Flysystem\Filesystem;

final readonly class SettingsImporter implements SingleImport
{
    public function __construct(
        private FileAccess $fileAccess,
        private PathRegistry $pathRegistry,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        $this->pathRegistry->get('storage');

        if ($settings->overwriteSettings === false && $this->fileAccess->exists('storage', 'settings.json')) {
            return;
        }

        $content = $filesystem->read('settings.json');
        $this->fileAccess->write('storage', 'settings.json', $content);
    }
}
