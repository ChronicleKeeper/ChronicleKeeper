<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

final readonly class FindAllImagesOfRequestQuery implements Query
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private DatabasePlatform $platform,
    ) {
    }

    /** @return list<GeneratorResult> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindAllImagesOfRequest);

        $files = $this->platform->fetch(
            'SELECT * FROM generator_results WHERE generatorRequest = :id',
            ['id' => $parameters->requestId],
        );

        $images = [];
        foreach ($files as $file) {
            try {
                $images[] = $this->denormalizer->denormalize($file, GeneratorResult::class);
            } catch (NotFoundHttpException) {
                // The File could not be converted, maybe the connected image is not existing anymore delete it
                $this->platform->query('DELETE FROM generator_results WHERE id = :id', ['id' => $file->getId()]);
            }
        }

        return $images;
    }
}
