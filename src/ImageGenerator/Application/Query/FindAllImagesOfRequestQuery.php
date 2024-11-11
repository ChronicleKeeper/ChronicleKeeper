<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder as FinderContract;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

use const DIRECTORY_SEPARATOR;

final readonly class FindAllImagesOfRequestQuery implements Query
{
    public function __construct(
        private PathRegistry $pathRegistry,
        private FileAccess $fileAccess,
        private SerializerInterface $serializer,
        private FinderContract $finder,
    ) {
    }

    /** @return list<GeneratorResult> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindAllImagesOfRequest);

        $requestImagesDirectory = DIRECTORY_SEPARATOR . $parameters->requestId;

        $files = $this->finder->findFilesInDirectoryOrderedByAccessTimestamp(
            $this->pathRegistry->get('generator.images') . $requestImagesDirectory,
        );

        $images = [];
        foreach ($files as $file) {
            $filename = $file->getFilename();
            assert($filename !== '');

            $content = $this->fileAccess->read(
                'generator.images',
                $requestImagesDirectory . DIRECTORY_SEPARATOR . $filename,
            );
            assert($content !== '');

            try {
                $images[] = $this->serializer->deserialize(
                    $content,
                    GeneratorResult::class,
                    JsonEncoder::FORMAT,
                );
            } catch (NotFoundHttpException) {
                // The File could not be converted, maybe the connected image is not existing anymore delete it
                $this->fileAccess->delete(
                    'generator.images',
                    $requestImagesDirectory . DIRECTORY_SEPARATOR . $filename,
                );
            }
        }

        return $images;
    }
}
