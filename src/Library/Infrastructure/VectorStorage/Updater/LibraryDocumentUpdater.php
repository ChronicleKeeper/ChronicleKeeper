<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\VectorStorage\Updater;

use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDocumentRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorDocumentRepository;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorDocument;
use PhpLlm\LlmChain\EmbeddingsModel;
use Psr\Log\LoggerInterface;

use function reset;

class LibraryDocumentUpdater
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EmbeddingsModel $embeddings,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly FilesystemVectorDocumentRepository $vectorDocumentRepository,
    ) {
    }

    public function updateAll(): void
    {
        foreach ($this->documentRepository->findAll() as $document) {
            $this->updateOrCreateVectorsForDocument($document);
        }
    }

    private function updateOrCreateVectorsForDocument(Document $document): void
    {
        $existingStorage = $this->vectorDocumentRepository->findAllByDocumentId($document->id);

        if ($existingStorage === []) {
            $this->createVectorDocument($document);

            return;
        }

        // Update
        $originalVectorDocument = reset($existingStorage);

        if ($originalVectorDocument->vectorContentHash === $document->getContentHash()) {
            $this->logger->debug('Vector storage for document is up to date.', ['document' => $document]);

            return;
        }

        $originalVectorDocument->vectorContentHash = $document->getContentHash();
        $originalVectorDocument->vector            = $this->embeddings->create($document->content)->getData();

        $this->vectorDocumentRepository->store($originalVectorDocument);

        $this->logger->debug('Vector storage for document was updated.', ['document' => $document]);
    }

    private function createVectorDocument(Document $document): void
    {
        $vectorDocument = new VectorDocument(
            document: $document,
            vectorContentHash: $document->getContentHash(),
            vector: $this->embeddings->create($document->content)->getData(),
        );

        $this->vectorDocumentRepository->store($vectorDocument);

        $this->logger->debug('Vector storage for document was created.', ['document' => $document]);
    }
}
