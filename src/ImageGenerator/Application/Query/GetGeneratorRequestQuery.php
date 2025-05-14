<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;
use function json_decode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final readonly class GetGeneratorRequestQuery implements Query
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private Connection $connection,
    ) {
    }

    public function query(QueryParameters $parameters): GeneratorRequest
    {
        assert($parameters instanceof GetGeneratorRequest);

        $request = $this->fetchGeneratorRequest($parameters->id);

        if ($request === null) {
            throw new InvalidArgumentException(sprintf(
                'Generator request with id "%s" not found.',
                $parameters->id,
            ));
        }

        return $this->hydrateResult($request);
    }

    /** @return array<string, mixed>|null */
    private function fetchGeneratorRequest(string $id): array|null
    {
        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('generator_requests')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();

        return $result === false ? null : $result;
    }

    /** @param array<string, mixed> $request */
    private function hydrateResult(array $request): GeneratorRequest
    {
        $request['userInput'] = json_decode(
            (string) $request['userInput'],
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        return $this->denormalizer->denormalize($request, GeneratorRequest::class);
    }
}
