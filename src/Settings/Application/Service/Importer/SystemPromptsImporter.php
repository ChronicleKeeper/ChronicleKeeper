<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;

use const DIRECTORY_SEPARATOR;

final readonly class SystemPromptsImporter implements SingleImport
{
    public function __construct(
        private FileAccess $fileAccess,
        private PathRegistry $pathRegistry,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): ImportedFileBag
    {
        $file = $this->pathRegistry->get('storage') . DIRECTORY_SEPARATOR . 'system_prompts.json';

        if ($settings->overwriteSettings === false) {
            return new ImportedFileBag(ImportedFile::asIgnored(
                $file,
                FileType::SYSTEM_PROMPTS,
                'Settings overwrite is disabled.',
            ));
        }

        try {
            $content = $filesystem->read('system_prompts.json');
            $this->fileAccess->write('storage', 'system_prompts.json', $content);

            return new ImportedFileBag(ImportedFile::asSuccess($file, FileType::SYSTEM_PROMPTS));
        } catch (UnableToReadFile) {
            return new ImportedFileBag(ImportedFile::asIgnored(
                $file,
                FileType::SYSTEM_PROMPTS,
                'File not in archive.',
            ));
        }
    }
}
