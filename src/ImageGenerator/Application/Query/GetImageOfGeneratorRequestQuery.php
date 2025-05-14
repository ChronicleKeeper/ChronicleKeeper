<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;
use function sprintf;

final readonly class GetImageOfGeneratorRequestQuery implements Query
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private Connection $connection,
    ) {
    }

    public function query(QueryParameters $parameters): GeneratorResult
    {
        assert($parameters instanceof GetImageOfGeneratorRequest);

        $image = $this->fetchImage($parameters->requestId, $parameters->imageId);

        if ($image === null) {
            throw new InvalidArgumentException(sprintf(
                'Generator result with requestId "%s" and imageId "%s" not found.',
                $parameters->requestId,
                $parameters->imageId,
            ));
        }

        return $this->denormalizer->denormalize($image, GeneratorResult::class);
    }

    /** @return array<string, mixed>|null */
    private function fetchImage(string $requestId, string $imageId): array|null
    {
        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('generator_results')
            ->where('"generatorRequest" = :requestId')
            ->andWhere('id = :imageId')
            ->setParameter('requestId', $requestId)
            ->setParameter('imageId', $imageId)
            ->executeQuery()
            ->fetchAssociative();

        return $result === false ? null : $result;
    }
}
