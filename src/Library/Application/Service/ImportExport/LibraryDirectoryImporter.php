<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\ImportExport;

use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use Doctrine\DBAL\Connection;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

use function assert;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final readonly class LibraryDirectoryImporter implements SingleImport
{
    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        // Import with version < 0.7
        if ($filesystem->fileExists('library/directories.json') === false) {
            $this->handleOlderImports($filesystem, $settings);

            return;
        }

        $content = $filesystem->read('library/directories.json');
        $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        foreach ($content['data'] as $directoryArray) {
            if ($directoryArray['id'] === RootDirectory::ID) {
                // The root directory should not be part of the import, but when it is there we ignore it
                continue;
            }

            if ($settings->overwriteLibrary === false && $this->hasDirectory($directoryArray['id'])) {
                $this->logger->debug(
                    'Directory already exists in the database, skipping import',
                    ['id' => $directoryArray['id']],
                );

                continue;
            }

            // Upsert using explicit check
            if ($this->hasDirectory($directoryArray['id'])) {
                $this->connection->createQueryBuilder()
                    ->update('directories')
                    ->set('title', ':title')
                    ->set('parent', ':parent')
                    ->where('id = :id')
                    ->setParameters([
                        'id' => $directoryArray['id'],
                        'title' => $directoryArray['title'],
                        'parent' => $directoryArray['parent'],
                    ])
                    ->executeStatement();
            } else {
                $this->connection->createQueryBuilder()
                    ->insert('directories')
                    ->values([
                        'id' => ':id',
                        'title' => ':title',
                        'parent' => ':parent',
                    ])
                    ->setParameters([
                        'id' => $directoryArray['id'],
                        'title' => $directoryArray['title'],
                        'parent' => $directoryArray['parent'],
                    ])
                    ->executeStatement();
            }

            $this->logger->debug('Imported directory', ['id' => $directoryArray['id']]);
        }
    }

    private function handleOlderImports(Filesystem $filesystem, ImportSettings $settings): void
    {
        $libraryDirectoryPath = 'library/directory/';
        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $content = $filesystem->read($zippedFile->path());
            $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if ($settings->overwriteLibrary === false && $this->hasDirectory($content['id'])) {
                $this->logger->debug(
                    'Directory already exists in the database, skipping import',
                    ['id' => $content['id']],
                );

                continue;
            }

            // Upsert using explicit check
            if ($this->hasDirectory($content['id'])) {
                $this->connection->createQueryBuilder()
                    ->update('directories')
                    ->set('title', ':title')
                    ->set('parent', ':parent')
                    ->where('id = :id')
                    ->setParameters([
                        'id' => $content['id'],
                        'title' => $content['title'],
                        'parent' => $content['parent'],
                    ])
                    ->executeStatement();
            } else {
                $this->connection->createQueryBuilder()
                    ->insert('directories')
                    ->values([
                        'id' => ':id',
                        'title' => ':title',
                        'parent' => ':parent',
                    ])
                    ->setParameters([
                        'id' => $content['id'],
                        'title' => $content['title'],
                        'parent' => $content['parent'],
                    ])
                    ->executeStatement();
            }

            $this->logger->debug('Imported directory from old format', ['id' => $content['id']]);
        }
    }

    private function hasDirectory(string $id): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('directories')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchOne();

        return $result !== false;
    }
}
