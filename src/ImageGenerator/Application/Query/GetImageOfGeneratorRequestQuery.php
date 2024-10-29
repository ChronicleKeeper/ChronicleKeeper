<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

use const DIRECTORY_SEPARATOR;

final readonly class GetImageOfGeneratorRequestQuery implements Query
{
    public function __construct(
        private FileAccess $fileAccess,
        private SerializerInterface $serializer,
    ) {
    }

    public function query(QueryParameters $parameters): GeneratorResult
    {
        assert($parameters instanceof GetImageOfGeneratorRequest);

        $filename = $parameters->requestId . DIRECTORY_SEPARATOR . $parameters->imageId . '.json';

        return $this->serializer->deserialize(
            $this->fileAccess->read('generator.images', $filename),
            GeneratorResult::class,
            JsonEncoder::FORMAT,
        );
    }
}
