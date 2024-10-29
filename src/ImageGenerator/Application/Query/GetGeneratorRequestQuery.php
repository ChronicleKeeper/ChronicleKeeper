<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

final readonly class GetGeneratorRequestQuery implements Query
{
    public function __construct(
        private FileAccess $fileAccess,
        private SerializerInterface $serializer,
    ) {
    }

    public function query(QueryParameters $parameters): GeneratorRequest
    {
        assert($parameters instanceof GetGeneratorRequest);

        return $this->serializer->deserialize(
            $content = $this->fileAccess->read('generator.requests', $parameters->id . '.json'),
            GeneratorRequest::class,
            JsonEncoder::FORMAT,
        );
    }
}
