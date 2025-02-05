<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service\ImportExport;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
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
        private DatabasePlatform $databasePlatform,
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

        $this->databasePlatform->createQueryBuilder()->createInsert()
            ->asReplace()
            ->insert('documents')
            ->values([
                'id' => $content['id'],
                'title' => $content['title'],
                'content' => $content['content'],
                'directory' => $content['directory'],
                'last_updated' => $content['last_updated'],
            ])
            ->execute();
    }

    private function hasDocument(string $id): bool
    {
        return $this->databasePlatform->createQueryBuilder()->createSelect()
            ->select('id')
            ->from('documents')
            ->where('id', '=', $id)
            ->fetchOneOrNull() !== null;
    }
}
