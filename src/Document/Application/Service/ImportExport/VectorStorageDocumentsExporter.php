<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service\ImportExport;

use ChronicleKeeper\Document\Application\Query\FindAllDocuments;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportData;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportSettings;
use ChronicleKeeper\Settings\Application\Service\Exporter\SingleExport;
use ChronicleKeeper\Settings\Application\Service\Exporter\Type;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

use function count;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class VectorStorageDocumentsExporter implements SingleExport
{
    public function __construct(
        private QueryService $queryService,
        private SerializerInterface $serializer,
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        /** @var Document[] $documents */
        $documents = $this->queryService->query(new FindAllDocuments());
        if (count($documents) === 0) {
            $this->logger->debug('No documents found, skipping export of vector storage.');

            return;
        }

        foreach ($documents as $document) {
            $embeddingsQuery = $this->connection->createQueryBuilder()
                ->select('document_id', 'content', 'vectorContentHash', 'JSON(embedding) as embedding')
                ->from('documents_vectors')
                ->where('document_id = :documentId')
                ->setParameter('documentId', $document->getId());

            $embeddingsOfDocument = $embeddingsQuery->executeQuery()->fetchAllAssociative();

            if (count($embeddingsOfDocument) === 0) {
                $this->logger->debug(
                    'No embeddings found for document, skipping export.',
                    ['id' => $document->getId()],
                );

                continue;
            }

            $this->logger->debug('Exporting document embeddings.', ['id' => $document->getId()]);

            $archive->addFromString(
                'library/document_embeddings/' . $document->getId() . '.json',
                $this->serializer->serialize(
                    ExportData::create(
                        $exportSettings,
                        Type::DOCUMENT_EMBEDDING,
                        $embeddingsOfDocument,
                    ),
                    'json',
                    ['json_encode_options' => JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR],
                ),
            );
        }
    }
}
