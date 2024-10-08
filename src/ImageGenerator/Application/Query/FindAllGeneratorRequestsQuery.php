<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\SymfonyFinder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

final class FindAllGeneratorRequestsQuery implements Query
{
    public function __construct(
        private readonly PathRegistry $pathRegistry,
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly SymfonyFinder $finder,
    ) {
    }

    /** @return list<GeneratorRequest> */
    public function query(QueryParameters $parameters): array
    {
        $files = $this->finder->findFilesInDirectory(
            $this->pathRegistry->get('generator.images'),
        );

        $requests = [];
        foreach ($files as $file) {
            $filename = $file->getFilename();
            assert($filename !== '');

            $content = $this->fileAccess->read('generator.images', $filename);
            assert($content !== '');

            $requests[] = $this->serializer->deserialize(
                $content,
                GeneratorRequest::class,
                JsonEncoder::FORMAT,
            );
        }

        return $requests;
    }
}
