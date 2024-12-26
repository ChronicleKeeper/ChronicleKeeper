<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Infrastructure\VectorStorage;

use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectors;
use ChronicleKeeper\Document\Application\Command\StoreDocumentVectors;
use ChronicleKeeper\Document\Application\Query\FindAllDocuments;
use ChronicleKeeper\Document\Application\Query\FindVectorsOfDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Domain\Entity\VectorDocument;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use function count;
use function reset;
use function trim;

class LibraryDocumentUpdater
{
    private const int VECTOR_CONTENT_LENGTH = 800;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EmbeddingCalculator $embeddingCalculator,
        private readonly QueryService $queryService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function updateAll(int $contentLength = self::VECTOR_CONTENT_LENGTH): void
    {
        foreach ($this->queryService->query(new FindAllDocuments()) as $document) {
            $this->updateOrCreateVectorsForDocument($document, $contentLength);
        }
    }

    public function updateOrCreateVectorsForDocument(
        Document $document,
        int $contentLength = self::VECTOR_CONTENT_LENGTH,
    ): void {
        $existingStorage = $this->queryService->query(new FindVectorsOfDocument($document->getId()));

        if ($existingStorage === []) {
            $this->createVectorDocument($document, $contentLength);

            return;
        }

        // Check the existing storage content hash and update only if necessary
        $singleOriginalVectorDocument = reset($existingStorage);
        if ($singleOriginalVectorDocument->vectorContentHash === $document->getContentHash()) {
            $this->logger->debug('Vector storage for document is up to date.', ['document' => $document]);

            return;
        }

        // Clean Vector Storage
        foreach ($existingStorage as $vectorDocument) {
            $this->bus->dispatch(new DeleteDocumentVectors($vectorDocument->id));
        }

        $this->logger->debug('Vector storage for document was cleared.', ['document' => $document]);

        // Re-Create the Storage
        $this->createVectorDocument($document, $contentLength);
    }

    private function createVectorDocument(Document $document, int $contentLength): void
    {
        $vectorDocuments = $this->splitDocumentInVectorDocuments($document, $contentLength);
        foreach ($vectorDocuments as $vectorDocument) {
            $this->bus->dispatch(new StoreDocumentVectors($vectorDocument));
        }

        $this->logger->debug(
            'Vector storage for document was created.',
            ['document' => $document, 'vectorDocumentsAmount' => count($vectorDocuments)],
        );
    }

    /** @return VectorDocument[] */
    private function splitDocumentInVectorDocuments(
        Document $document,
        int $contentLength,
    ): array {
        // Calculate Content Chunks
        $contentChunks = $this->embeddingCalculator->createTextChunks($document->getContent(), $contentLength);
        // Calculate vectors from the chunks
        $vectors = $this->embeddingCalculator->getMultipleEmbeddings($contentChunks);

        $vectorDocuments = [];
        foreach ($contentChunks as $index => $chunk) {
            $vectorDocuments[] = new VectorDocument(
                document: $document,
                content: trim($chunk),
                vectorContentHash: $document->getContentHash(),
                vector: $vectors[$index],
            );
        }

        return $vectorDocuments;
    }
}
