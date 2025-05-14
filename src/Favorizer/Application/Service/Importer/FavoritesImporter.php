<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use Doctrine\DBAL\Connection;
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
        private Connection $connection,
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
            // Current workaround to support import format < 0.7
            $content = $content['data'];

            $this->logger->debug('Imported favorites from new format', ['count' => count($content)]);
        } else {
            $this->logger->debug('Imported favorites from old format', ['count' => count($content)]);
        }

        foreach ($content as $row) {
            // Check if the favorite already exists
            $exists = $this->favoriteExists($row['id']);

            if ($exists) {
                // Update existing favorite
                $this->connection->createQueryBuilder()
                    ->update('favorites')
                    ->set('type', ':type')
                    ->set('title', ':title')
                    ->where('id = :id')
                    ->setParameters([
                        'id' => $row['id'],
                        'type' => $row['type'],
                        'title' => $row['title'],
                    ])
                    ->executeStatement();
            } else {
                // Insert new favorite
                $this->connection->createQueryBuilder()
                    ->insert('favorites')
                    ->values([
                        'id' => ':id',
                        'type' => ':type',
                        'title' => ':title',
                    ])
                    ->setParameters([
                        'id' => $row['id'],
                        'type' => $row['type'],
                        'title' => $row['title'],
                    ])
                    ->executeStatement();
            }
        }
    }

    private function favoriteExists(string $id): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('favorites')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchOne();

        return $result !== false;
    }
}
