<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function json_decode;

use const JSON_THROW_ON_ERROR;

final readonly class FindAllGeneratorRequestsQuery implements Query
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private DatabasePlatform $databasePlatform,
    ) {
    }

    /** @return list<GeneratorRequest> */
    public function query(QueryParameters $parameters): array
    {
        $files = $this->databasePlatform->fetch('SELECT * FROM generator_requests ORDER BY title');

        $requests = [];
        foreach ($files as $file) {
            $file['userInput'] = json_decode((string) $file['userInput'], true, 512, JSON_THROW_ON_ERROR);

            $requests[] = $this->denormalizer->denormalize($file, GeneratorRequest::class);
        }

        return $requests;
    }
}
