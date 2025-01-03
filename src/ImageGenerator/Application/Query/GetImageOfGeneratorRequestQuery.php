<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

final readonly class GetImageOfGeneratorRequestQuery implements Query
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private DatabasePlatform $platform,
    ) {
    }

    public function query(QueryParameters $parameters): GeneratorResult
    {
        assert($parameters instanceof GetImageOfGeneratorRequest);

        $images = $this->platform->fetch(
            'SELECT * FROM generator_results WHERE generatorRequest = :requestId AND id = :imageId',
            ['requestId' => $parameters->requestId, 'imageId' => $parameters->imageId],
        );

        return $this->denormalizer->denormalize($images[0], GeneratorResult::class);
    }
}
