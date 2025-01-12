<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;

final readonly class SystemPromptsImporter implements SingleImport
{
    public function __construct(
        private FileAccess $fileAccess,
        private PathRegistry $pathRegistry,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        $this->pathRegistry->get('storage');

        if ($settings->overwriteSettings === false) {
            return;
        }

        try {
            $content = $filesystem->read('system_prompts.json');
            $this->fileAccess->write('storage', 'system_prompts.json', $content);
        } catch (UnableToReadFile) {
            // It is totally fine when this fails as there are maybe no user created system prompts
        }
    }
}
