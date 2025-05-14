<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

final readonly class FindAllImagesOfRequestQuery implements Query
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private Connection $connection,
    ) {
    }

    /** @return list<GeneratorResult> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindAllImagesOfRequest);

        $results = $this->fetchGeneratorResults($parameters->requestId);

        return $this->hydrateResults($results);
    }

    /** @return array<int, array<string, mixed>> */
    private function fetchGeneratorResults(string $requestId): array
    {
        return $this->connection->createQueryBuilder()
            ->select('*')
            ->from('generator_results')
            ->where('"generatorRequest" = :requestId')
            ->setParameter('requestId', $requestId)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @param array<int, array<string, mixed>> $results
     *
     * @return list<GeneratorResult>
     */
    private function hydrateResults(array $results): array
    {
        $images = [];
        foreach ($results as $result) {
            $images[] = $this->denormalizer->denormalize($result, GeneratorResult::class);
        }

        return $images;
    }
}
