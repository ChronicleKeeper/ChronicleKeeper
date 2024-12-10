<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Infrastructure\VectorStorage;

use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use Psr\Log\LoggerInterface;

use function count;
use function reset;
use function trim;

class LibraryImageUpdater
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EmbeddingCalculator $embeddingCalculator,
        private readonly FilesystemImageRepository $imageRepository,
        private readonly FilesystemVectorImageRepository $vectorImageRepository,
    ) {
    }

    public function updateAll(): void
    {
        foreach ($this->imageRepository->findAll() as $image) {
            $this->updateOrCreateVectorsForImage($image);
        }
    }

    private function updateOrCreateVectorsForImage(Image $image): void
    {
        $existingStorage = $this->vectorImageRepository->findAllByImageId($image->id);

        if ($existingStorage === []) {
            $this->createVectorDocument($image);

            return;
        }

        // Check the existing storage content hash and update only if necessary
        $singleOriginalVectorDocument = reset($existingStorage);
        if ($singleOriginalVectorDocument->vectorContentHash === $image->getDescriptionHash()) {
            $this->logger->debug('Vector storage for image is up to date.', ['image' => $image]);

            return;
        }

        // Clean Vector Storage
        foreach ($existingStorage as $vectorDocument) {
            $this->vectorImageRepository->remove($vectorDocument);
        }

        $this->logger->debug('Vector storage for document was cleared.', ['image' => $image]);

        // Re-Create the Storage
        $this->createVectorDocument($image);
    }

    private function createVectorDocument(Image $image): void
    {
        $vectorDocuments = $this->splitImageDescriptionInVectorDocuments($image);
        foreach ($vectorDocuments as $vectorDocument) {
            $this->vectorImageRepository->store($vectorDocument);
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
        $contentChunks = $this->embeddingCalculator->createTextChunks($image->description);
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
