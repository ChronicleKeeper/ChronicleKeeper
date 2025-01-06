<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;
use function count;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final readonly class GetGeneratorRequestQuery implements Query
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private DatabasePlatform $platform,
    ) {
    }

    public function query(QueryParameters $parameters): GeneratorRequest
    {
        assert($parameters instanceof GetGeneratorRequest);

        $request = $this->platform->fetchSingleRow(
            'SELECT * FROM generator_requests WHERE id = :id',
            ['id' => $parameters->id],
        );

        if (count($request) === 0) {
            throw new InvalidArgumentException('Generator Request not found');
        }

        $request['userInput'] = json_decode(
            (string) $request['userInput'],
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        return $this->denormalizer->denormalize($request, GeneratorRequest::class);
    }
}
