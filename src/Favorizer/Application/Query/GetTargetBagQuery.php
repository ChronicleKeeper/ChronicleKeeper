<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Query;

use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;
use function count;

final class GetTargetBagQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function query(QueryParameters $parameters): TargetBag
    {
        assert($parameters instanceof GetTargetBag);

        $content = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->from('favorites')
            ->orderBy('title')
            ->fetchAll();

        if (count($content) === 0) {
            return new TargetBag();
        }

        return new TargetBag(...$this->denormalizer->denormalize($content, Target::class . '[]'));
    }
}
