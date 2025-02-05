<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;
use Psr\Log\LoggerInterface;

use function array_key_exists;
use function count;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final readonly class FavoritesImporter implements SingleImport
{
    public function __construct(
        private DatabasePlatform $databasePlatform,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        try {
            $content = $filesystem->read('favorites.json');
        } catch (UnableToReadFile) {
            // It is totally fine, when the file is not available during import
            return;
        }

        $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (array_key_exists('appVersion', $content)) {
            // Current woraround to support import format < 0.7
            $content = $content['data'];

            $this->logger->debug('Imported favorites from new format', ['count' => count($content)]);
        } else {
            $this->logger->debug('Imported favorites from old format', ['count' => count($content)]);
        }

        foreach ($content as $row) {
            $this->databasePlatform->createQueryBuilder()->createInsert()
                ->asReplace()
                ->insert('favorites')
                ->values([
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'title' => $row['title'],
                ])
                ->execute();
        }
    }
}
