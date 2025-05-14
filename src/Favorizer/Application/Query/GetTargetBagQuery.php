<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Query;

use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;
use function count;

final class GetTargetBagQuery implements Query
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly Connection $connection,
    ) {
    }

    public function query(QueryParameters $parameters): TargetBag
    {
        assert($parameters instanceof GetTargetBag);

        $content = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('favorites')
            ->orderBy('title')
            ->executeQuery()
            ->fetchAllAssociative();

        if (count($content) === 0) {
            return new TargetBag();
        }

        return new TargetBag(...$this->denormalizer->denormalize($content, Target::class . '[]'));
    }
}
