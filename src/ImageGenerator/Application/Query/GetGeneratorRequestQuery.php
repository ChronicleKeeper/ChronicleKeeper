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
use function json_decode;
use function sprintf;

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

        $request = $this->platform->createQueryBuilder()->createSelect()
            ->from('generator_requests')
            ->where('id', '=', $parameters->id)
            ->fetchOneOrNull();

        if ($request === null) {
            throw new InvalidArgumentException(sprintf(
                'Generator request with id "%s" not found.',
                $parameters->id,
            ));
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
