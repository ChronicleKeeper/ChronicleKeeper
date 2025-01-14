<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service\ImportExport;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

use function assert;
use function count;
use function implode;
use function json_decode;
use function reset;
use function str_replace;

use const JSON_THROW_ON_ERROR;

final readonly class VectorStorageDocumentsImporter implements SingleImport
{
    public function __construct(
        private DatabasePlatform $databasePlatform,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        if (count($filesystem->listContents('vector/document/')->toArray()) > 0) {
            $this->logger->debug('Starting the classic import of the vector storage documents.');
            $this->classicImport($filesystem, $settings);

            return;
        }

        $this->logger->debug('Utilizing the modern import of the vector storage documents.');

        foreach ($filesystem->listContents('library/document_embeddings/') as $file) {
            assert($file instanceof FileAttributes);

            $fileContent = $filesystem->read($file->path());
            $content     = json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);

            if (count($content['data']) === 0) {
                // Vector Storage is empty, no need to store something
                continue;
            }

            $documentId = reset($content['data'])['document_id'];
            if (
                $settings->overwriteLibrary === false
                && $this->databasePlatform->hasRows('documents_vectors', ['document_id' => $documentId])
            ) {
                // The document already has a vector storage, no need to overwrite
                continue;
            }

            $this->databasePlatform->query('DELETE FROM documents_vectors WHERE document_id = :documentId', ['documentId' => $documentId]);
            foreach ($content['data'] as $row) {
                $this->databasePlatform->insert(
                    'documents_vectors',
                    [
                        'document_id' => $row['document_id'],
                        'embedding' => $row['embedding'],
                        'content' => $row['content'],
                        'vectorContentHash' => $row['vectorContentHash'],
                    ],
                );
            }
        }
    }

    private function classicImport(Filesystem $filesystem, ImportSettings $settings): void
    {
        if ($settings->overwriteLibrary === false) {
            $this->logger->debug('Skipping the classic import of the vector storage documents, as the overwrite is disabled.');

            return;
        }

        $libraryDirectoryPath = 'vector/document/';
        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $filename = str_replace($libraryDirectoryPath, '', $zippedFile->path());
            assert($filename !== '');

            $fileContent = $filesystem->read($zippedFile->path());

            /** @var array{documentId: string, content: string, vectorContentHash: string, vector: list<float>} $content */
            $content = json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);

            $this->databasePlatform->query('DELETE FROM documents_vectors WHERE document_id = :documentId', ['documentId' => $content['documentId']]);
            $this->databasePlatform->insertOrUpdate(
                'documents_vectors',
                [
                    'document_id' => $content['documentId'],
                    'embedding' => '[' . implode(',', $content['vector']) . ']',
                    'content' => $content['content'],
                    'vectorContentHash' => $content['vectorContentHash'],
                ],
            );
        }
    }
}
