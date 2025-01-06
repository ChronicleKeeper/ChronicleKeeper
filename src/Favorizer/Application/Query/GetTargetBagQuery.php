<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Query;

use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function count;

final readonly class GetTargetBagQuery implements Query
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private DatabasePlatform $databasePlatform,
    ) {
    }

    public function query(QueryParameters $parameters): TargetBag
    {
        $content = $this->databasePlatform->fetch('SELECT * FROM favorites');
        if (count($content) === 0) {
            return new TargetBag();
        }

        return new TargetBag(...$this->denormalizer->denormalize($content, Target::class . '[]'));
    }
}
