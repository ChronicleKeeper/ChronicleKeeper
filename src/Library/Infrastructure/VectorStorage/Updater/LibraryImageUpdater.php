<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\VectorStorage\Updater;

use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use Psr\Log\LoggerInterface;

use function count;
use function reset;
use function strlen;
use function substr;
use function Symfony\Component\String\u;
use function trim;

class LibraryImageUpdater
{
    private const int VECTOR_CONTENT_LENGTH = 800;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LLMChainFactory $embeddings,
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
        $vectorDocuments = $this->splitImageDescriptionInVectorDocuments($image, self::VECTOR_CONTENT_LENGTH);
        foreach ($vectorDocuments as $vectorDocument) {
            $this->vectorImageRepository->store($vectorDocument);
        }

        $this->logger->debug(
            'Vector storage for image was created.',
            ['image' => $image, 'vectorDocumentsAmount' => count($vectorDocuments)],
        );
    }

    /** @return VectorImage[] */
    private function splitImageDescriptionInVectorDocuments(
        Image $image,
        int $vectorContentLength,
    ): array {
        $content         = $image->description;
        $vectorDocuments = [];

        $embeddingModel = $this->embeddings->createEmbeddings();
        do {
            $vectorContent = u($content)->truncate($vectorContentLength, '', false)->toString();
            $content       = substr($content, strlen($vectorContent));

            $vectorDocument = new VectorImage(
                image: $image,
                content: trim($vectorContent),
                vectorContentHash: $image->getDescriptionHash(),
                vector: $embeddingModel->create($vectorContent)->getData(),
            );

            $vectorDocuments[] = $vectorDocument;
        } while ($content !== '');

        return $vectorDocuments;
    }
}
