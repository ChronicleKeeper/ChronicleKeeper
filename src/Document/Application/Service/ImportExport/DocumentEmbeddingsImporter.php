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
use function json_decode;
use function reset;

use const JSON_THROW_ON_ERROR;

final readonly class DocumentEmbeddingsImporter implements SingleImport
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
            $this->classicImport($filesystem);

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
            if ($settings->overwriteLibrary === false && $this->hasDocumentVectors($documentId)) {
                // The document already has a vector storage, no need to overwrite
                $this->logger->debug(
                    'Document already has a vector storage, skipping.',
                    ['document_id' => $documentId],
                );
                continue;
            }

            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('documents_vectors')
                ->where('document_id', '=', $documentId)
                ->execute();

            foreach ($content['data'] as $row) {
                $this->databasePlatform->createQueryBuilder()->createInsert()
                    ->insert('documents_vectors')
                    ->values([
                        'document_id' => $row['document_id'],
                        'embedding' => $row['embedding'],
                        'content' => $row['content'],
                        'vectorContentHash' => $row['vectorContentHash'],
                    ])
                    ->execute();
            }

            $this->logger->debug('Document vector storage imported.', ['document_id' => $documentId]);
        }
    }

    private function classicImport(Filesystem $filesystem): void
    {
        $libraryDirectoryPath = 'vector/document/';
        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $fileContent = $filesystem->read($zippedFile->path());

            /** @var array{id: string, documentId: string, content: string, vectorContentHash: string, vector: list<float>} $content */
            $content = json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);

            $this->databasePlatform->createQueryBuilder()->createDelete()
                ->from('documents_vectors')
                ->where('document_id', '=', $content['documentId'])
                ->execute();

            $this->databasePlatform->createQueryBuilder()->createInsert()
                ->insert('documents_vectors')
                ->values([
                    'document_id' => $content['documentId'],
                    'embedding' => $content['embedding'],
                    'content' => $content['content'],
                    'vectorContentHash' => $content['vectorContentHash'],
                ])
                ->execute();

            $this->logger->debug('Document vector storage imported.', ['vector_id' => $content['id']]);
        }
    }

    private function hasDocumentVectors(string $id): bool
    {
        return $this->databasePlatform->createQueryBuilder()->createSelect()
                ->select('document_id')
                ->from('documents_vectors')
                ->where('document_id', '=', $id)
                ->fetchOneOrNull() !== null;
    }
}
