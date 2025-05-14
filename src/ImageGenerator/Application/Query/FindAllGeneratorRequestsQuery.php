<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function json_decode;

use const JSON_THROW_ON_ERROR;

final readonly class FindAllGeneratorRequestsQuery implements Query
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private Connection $connection,
    ) {
    }

    /** @return list<GeneratorRequest> */
    public function query(QueryParameters $parameters): array
    {
        $results = $this->fetchGeneratorRequests();

        return $this->hydrateResults($results);
    }

    /** @return array<int, array<string, mixed>> */
    private function fetchGeneratorRequests(): array
    {
        return $this->connection->createQueryBuilder()
            ->select('*')
            ->from('generator_requests')
            ->orderBy('title')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @param array<int, array<string, mixed>> $results
     *
     * @return list<GeneratorRequest>
     */
    private function hydrateResults(array $results): array
    {
        $requests = [];

        foreach ($results as $result) {
            $result['userInput'] = json_decode((string) $result['userInput'], true, 512, JSON_THROW_ON_ERROR);
            $requests[]          = $this->denormalizer->denormalize($result, GeneratorRequest::class);
        }

        return $requests;
    }
}
