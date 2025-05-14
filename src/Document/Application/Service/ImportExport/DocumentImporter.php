<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service\ImportExport;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use Doctrine\DBAL\Connection;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

use function array_key_exists;
use function assert;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final readonly class DocumentImporter implements SingleImport
{
    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        foreach ($filesystem->listContents('library/document/') as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $this->importZippedFile($filesystem, $zippedFile, $settings);
        }

        // Fallback to new directory structure
        foreach ($filesystem->listContents('library/documents/') as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $this->importZippedFile($filesystem, $zippedFile, $settings);
        }
    }

    private function importZippedFile(Filesystem $filesystem, FileAttributes $file, ImportSettings $settings): void
    {
        $content = $filesystem->read($file->path());
        $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (array_key_exists('data', $content)) {
            // Workaround for Imports from versions < 0.7
            $content = $content['data'];
        }

        if ($settings->overwriteLibrary === false && $this->hasDocument($content['id'])) {
            $this->logger->debug('Document already exists, skipping.', ['document_id' => $content['id']]);

            return;
        }

        // Using upsert by checking if document exists first
        if ($this->hasDocument($content['id'])) {
            $this->connection->createQueryBuilder()
                ->update('documents')
                ->set('title', ':title')
                ->set('content', ':content')
                ->set('directory', ':directory')
                ->set('last_updated', ':lastUpdated')
                ->where('id = :id')
                ->setParameters([
                    'id' => $content['id'],
                    'title' => $content['title'],
                    'content' => $content['content'],
                    'directory' => $content['directory'],
                    'lastUpdated' => $content['last_updated'],
                ])
                ->executeStatement();
        } else {
            $this->connection->createQueryBuilder()
                ->insert('documents')
                ->values([
                    'id' => ':id',
                    'title' => ':title',
                    'content' => ':content',
                    'directory' => ':directory',
                    'last_updated' => ':lastUpdated',
                ])
                ->setParameters([
                    'id' => $content['id'],
                    'title' => $content['title'],
                    'content' => $content['content'],
                    'directory' => $content['directory'],
                    'lastUpdated' => $content['last_updated'],
                ])
                ->executeStatement();
        }
    }

    private function hasDocument(string $id): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('documents')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchOne();

        return $result !== false;
    }
}
