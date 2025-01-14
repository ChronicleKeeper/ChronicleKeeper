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

final readonly class LibraryDocumentImporter implements SingleImport
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

        if (
            $settings->overwriteLibrary === false
            && $this->databasePlatform->hasRows('documents', ['id' => $content['id']])
        ) {
            $this->logger->debug('Document already exists, skipping.', ['document_id' => $content['id']]);

            return;
        }

        if (array_key_exists('data', $content)) {
            // Workaround for Imports from versions < 0.7
            $content = $content['data'];
        }

        $this->logger->debug('Importing document.', ['document_id' => $content['id']]);
        $this->databasePlatform->insertOrUpdate(
            'documents',
            [
                'id' => $content['id'],
                'title' => $content['title'],
                'content' => $content['content'],
                'directory' => $content['directory'],
                'last_updated' => $content['last_updated'],
            ],
        );
    }
}
