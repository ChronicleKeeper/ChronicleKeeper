<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Image\Domain\Entity\SearchVector;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

class GetAllVectorSearchImagesQuery implements Query
{
    public function __construct(
        private readonly PathRegistry $pathRegistry,
        private readonly Finder $finder,
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return list<SearchVector> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof GetAllVectorSearchImages);

        $files = $this->finder->findFilesInDirectory($this->pathRegistry->get('vector.images'));

        $vectorImages = [];
        foreach ($files as $file) {
            try {
                $filename = $file->getFilename();
                assert($filename !== '');

                $content = $this->fileAccess->read('vector.images', $filename);
                assert($content !== '');

                $vectorImages[] = $this->serializer->deserialize($content, SearchVector::class, 'json');
            } catch (RuntimeException | UnableToReadFile $e) {
                $this->logger->error($e, ['file' => $file]);
            }
        }

        return $vectorImages;
    }
}
