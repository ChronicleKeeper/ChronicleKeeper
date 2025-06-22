<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\World\Domain\Entity\Item;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

class FindWorldLinksOfMediumQuery implements Query
{
    public function __construct(
        private readonly Connection $connection,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /** @return Item[] */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindWorldLinksOfMedium);

        $tableName = match ($parameters->type) {
            'document' => 'world_item_documents',
            'image' => 'world_item_images',
            'conversation' => 'world_item_conversations',
            default => throw new InvalidArgumentException('Unknown medium type: ' . $parameters->type),
        };

        $columnName = $parameters->type . '_id';

        $sql = <<<SQL
            SELECT
                id, type, name, short_description as shortDescription
            FROM world_items
            WHERE id IN (SELECT world_item_id FROM $tableName WHERE $columnName = :id)
            ORDER BY name
        SQL;

        $results = $this->connection->fetchAllAssociative($sql, ['id' => $parameters->mediumId]);

        return $this->denormalizer->denormalize(
            $results,
            Item::class . '[]',
        );
    }
}
