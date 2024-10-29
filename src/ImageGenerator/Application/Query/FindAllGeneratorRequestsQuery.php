<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;
use function strcasecmp;
use function usort;

final readonly class FindAllGeneratorRequestsQuery implements Query
{
    public function __construct(
        private PathRegistry $pathRegistry,
        private FileAccess $fileAccess,
        private SerializerInterface $serializer,
        private Finder $finder,
    ) {
    }

    /** @return list<GeneratorRequest> */
    public function query(QueryParameters $parameters): array
    {
        $files = $this->finder->findFilesInDirectory(
            $this->pathRegistry->get('generator.requests'),
        );

        $requests = [];
        foreach ($files as $file) {
            $filename = $file->getFilename();
            assert($filename !== '');

            $content = $this->fileAccess->read('generator.requests', $filename);
            assert($content !== '');

            $requests[] = $this->serializer->deserialize(
                $content,
                GeneratorRequest::class,
                JsonEncoder::FORMAT,
            );
        }

        usort(
            $requests,
            static fn (GeneratorRequest $left, GeneratorRequest $right) => strcasecmp($left->title, $right->title),
        );

        return $requests;
    }
}
