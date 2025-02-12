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

        $images = $this->platform->createQueryBuilder()->createSelect()
            ->from('generator_results')
            ->where('generatorRequest', '=', $parameters->requestId)
            ->where('id', '=', $parameters->imageId)
            ->fetchOne();

        return $this->denormalizer->denormalize($images, GeneratorResult::class);
    }
}
