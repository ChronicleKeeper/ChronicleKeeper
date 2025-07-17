<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service\ImportExport;

use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
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
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
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

            try {
                $this->connection->beginTransaction();

                // Delete existing vectors for this document
                $this->connection->createQueryBuilder()
                    ->delete('documents_vectors')
                    ->where('document_id = :documentId')
                    ->setParameter('documentId', $documentId)
                    ->executeStatement();

                // Insert new vectors
                foreach ($content['data'] as $row) {
                    $this->connection->createQueryBuilder()
                        ->insert('documents_vectors')
                        ->values([
                            'document_id' => ':documentId',
                            'embedding' => ':embedding',
                            'content' => ':content',
                            '"vectorContentHash"' => ':vectorContentHash',
                        ])
                        ->setParameters([
                            'documentId' => $row['document_id'],
                            'embedding' => $row['embedding'],
                            'content' => $row['content'],
                            'vectorContentHash' => $row['vectorContentHash'],
                        ])
                        ->executeStatement();
                }

                $this->connection->commit();
                $this->logger->debug('Document vector storage imported.', ['document_id' => $documentId]);
            } catch (Exception $e) {
                $this->connection->rollBack();
                $this->logger->error('Failed to import document vector storage', [
                    'document_id' => $documentId,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        }
    }

    private function hasDocumentVectors(string $id): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select('document_id')
            ->from('documents_vectors')
            ->where('document_id = :documentId')
            ->setParameter('documentId', $id)
            ->executeQuery()
            ->fetchOne();

        return $result !== false;
    }
}
