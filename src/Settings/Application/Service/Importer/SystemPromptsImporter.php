<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;
use Psr\Log\LoggerInterface;

use function array_key_exists;
use function json_decode;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class SystemPromptsImporter implements SingleImport
{
    public function __construct(
        private FileAccess $fileAccess,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        if ($settings->overwriteSettings === false) {
            $this->logger->debug('Skipping import of system prompts as overwrite is disabled.');

            return;
        }

        try {
            $content = $filesystem->read('system_prompts.json');

            // The following decoding and encoding is needed as a compatibility layer for the old format
            $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            if (array_key_exists('appVersion', $content)) {
                $content = $content['data'];
            }

            $this->fileAccess->write(
                'storage',
                'system_prompts.json',
                json_encode($content, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
            );
        } catch (UnableToReadFile) {
            // It is totally fine when this fails as there are maybe no user created system prompts
        }
    }
}
