<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ZipArchive;

use const DIRECTORY_SEPARATOR;

final readonly class SystemPromptsExporter implements SingleExport
{
    public function __construct(
        private PathRegistry $pathRegistry,
        private FileAccess $fileAccess,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        if (! $this->fileAccess->exists('storage', 'system_prompts.json')) {
            // Nothing to export, the user has no custom system prompts
            return;
        }

        $archive->addFile(
            $this->pathRegistry->get('storage') . DIRECTORY_SEPARATOR . 'system_prompts.json',
            'system_prompts.json',
        );
    }
}
