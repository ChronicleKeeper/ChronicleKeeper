<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\VectorStorage\Updater;

use DZunke\NovDoc\Domain\Library\Image\Image;
use DZunke\NovDoc\Domain\VectorStorage\VectorImage;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemImageRepository;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemVectorImageRepository;
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
