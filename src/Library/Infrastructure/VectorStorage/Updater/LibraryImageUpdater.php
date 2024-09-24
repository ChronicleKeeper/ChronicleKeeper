<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\VectorStorage\Updater;

use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use PhpLlm\LlmChain\EmbeddingModel;
use Psr\Log\LoggerInterface;

use function reset;

class LibraryImageUpdater
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EmbeddingModel $embeddings,
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

        // Update
        $originalVectorDocument = reset($existingStorage);

        if ($originalVectorDocument->vectorContentHash === $image->getDescriptionHash()) {
            $this->logger->debug('Vector storage for image is up to date.', ['image' => $image]);

            return;
        }

        $originalVectorDocument->vectorContentHash = $image->getDescriptionHash();
        $originalVectorDocument->vector            = $this->embeddings->create($image->description)->getData();

        $this->vectorImageRepository->store($originalVectorDocument);

        $this->logger->debug('Vector storage for image was updated.', ['image' => $image]);
    }

    private function createVectorDocument(Image $image): void
    {
        $vectorDocument = new VectorImage(
            image: $image,
            vectorContentHash: $image->getDescriptionHash(),
            vector: $this->embeddings->create($image->description)->getData(),
        );

        $this->vectorImageRepository->store($vectorDocument);

        $this->logger->debug('Vector storage for image was created.', ['image' => $image]);
    }
}
