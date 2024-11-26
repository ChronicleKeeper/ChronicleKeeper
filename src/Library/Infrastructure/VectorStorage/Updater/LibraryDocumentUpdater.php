<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\VectorStorage\Updater;

use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDocumentRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorDocumentRepository;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorDocument;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use Psr\Log\LoggerInterface;

use function assert;
use function count;
use function reset;
use function strlen;
use function substr;
use function Symfony\Component\String\u;
use function trim;

class LibraryDocumentUpdater
{
    private const int VECTOR_CONTENT_LENGTH = 800;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LLMChainFactory $embeddings,
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

        // Check the existing storage content hash and update only if necessary
        $singleOriginalVectorDocument = reset($existingStorage);
        if ($singleOriginalVectorDocument->vectorContentHash === $document->getContentHash()) {
            $this->logger->debug('Vector storage for document is up to date.', ['document' => $document]);

            return;
        }

        // Clean Vector Storage
        foreach ($existingStorage as $vectorDocument) {
            $this->vectorDocumentRepository->remove($vectorDocument);
        }

        $this->logger->debug('Vector storage for document was cleared.', ['document' => $document]);

        // Re-Create the Storage
        $this->createVectorDocument($document);
    }

    private function createVectorDocument(Document $document): void
    {
        $vectorDocuments = $this->splitDocumentInVectorDocuments($document, self::VECTOR_CONTENT_LENGTH);
        foreach ($vectorDocuments as $vectorDocument) {
            $this->vectorDocumentRepository->store($vectorDocument);
        }

        $this->logger->debug(
            'Vector storage for document was created.',
            ['document' => $document, 'vectorDocumentsAmount' => count($vectorDocuments)],
        );
    }

    /** @return VectorDocument[] */
    private function splitDocumentInVectorDocuments(
        Document $document,
        int $vectorContentLength,
    ): array {
        $content         = $document->content;
        $vectorDocuments = [];

        $platform = $this->embeddings->createPlatform();
        do {
            $vectorContent = u($content)->truncate($vectorContentLength, '', false)->toString();
            $content       = substr($content, strlen($vectorContent));

            $vector = $platform->request(
                model: $this->embeddings->createEmbeddingsModel(),
                input: $vectorContent,
            );
            assert($vector instanceof AsyncResponse);
            $vector = $vector->unwrap();

            assert($vector instanceof VectorResponse);
            $vector = $vector->getContent()[0];

            $vectorDocument = new VectorDocument(
                document: $document,
                content: trim($vectorContent),
                vectorContentHash: $document->getContentHash(),
                vector: $vector->getData(),
            );

            $vectorDocuments[] = $vectorDocument;
        } while ($content !== '');

        return $vectorDocuments;
    }
}
