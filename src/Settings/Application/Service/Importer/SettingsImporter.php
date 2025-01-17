<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

use function array_key_exists;
use function json_decode;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class SettingsImporter implements SingleImport
{
    public function __construct(
        private FileAccess $fileAccess,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        if ($settings->overwriteSettings === false && $this->fileAccess->exists('storage', 'settings.json')) {
            $this->logger->info('Settings import skipped, as settings already exist and overwrite is disabled.');

            return;
        }

        $content = $filesystem->read('settings.json');

        // The json handling is needed to ensure compatibility with old versiosns
        $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('appVersion', $content)) {
            $content = $content['data'];
        }

        $this->fileAccess->write(
            'storage',
            'settings.json',
            json_encode($content, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
        );
    }
}
