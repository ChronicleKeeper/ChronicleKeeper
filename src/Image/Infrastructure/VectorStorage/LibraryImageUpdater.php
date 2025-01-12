<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Infrastructure\VectorStorage;

use ChronicleKeeper\Image\Application\Command\DeleteImageVectors;
use ChronicleKeeper\Image\Application\Command\StoreImageVectors;
use ChronicleKeeper\Image\Application\Query\FindAllImages;
use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use function count;
use function trim;

class LibraryImageUpdater
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EmbeddingCalculator $embeddingCalculator,
        private readonly QueryService $queryService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function updateAll(): void
    {
        foreach ($this->queryService->query(new FindAllImages()) as $image) {
            $this->updateOrCreateVectorsForImage($image);
        }
    }

    public function updateOrCreateVectorsForImage(Image $image): void
    {
        // Clean Vector Storage
        $this->bus->dispatch(new DeleteImageVectors($image->getId()));

        $this->logger->debug('Vector storage for document was cleared.', ['image' => $image]);

        // Re-Create the Storage
        $this->createVectorDocument($image);
    }

    private function createVectorDocument(Image $image): void
    {
        $vectorDocuments = $this->splitImageDescriptionInVectorDocuments($image);
        foreach ($vectorDocuments as $vectorDocument) {
            $this->bus->dispatch(new StoreImageVectors($vectorDocument));
        }

        $this->logger->debug(
            'Vector storage for image was created.',
            ['image' => $image, 'vectorDocumentsAmount' => count($vectorDocuments)],
        );
    }

    /** @return VectorImage[] */
    private function splitImageDescriptionInVectorDocuments(Image $image): array
    {
        // Calculate Content Chunks
        $contentChunks = $this->embeddingCalculator->createTextChunks($image->getDescription());
        // Calculate vectors from the chunks
        $vectors = $this->embeddingCalculator->getMultipleEmbeddings($contentChunks);

        $vectorImages = [];
        foreach ($contentChunks as $index => $chunk) {
            $vectorImages[] = new VectorImage(
                image: $image,
                content: trim($chunk),
                vectorContentHash: $image->getDescriptionHash(),
                vector: $vectors[$index],
            );
        }

        return $vectorImages;
    }
}
