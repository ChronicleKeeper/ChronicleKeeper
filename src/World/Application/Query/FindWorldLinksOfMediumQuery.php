<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\World\Domain\Entity\Item;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function assert;

class FindWorldLinksOfMediumQuery implements Query
{
    public function __construct(
        private readonly DatabasePlatform $platform,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /** @return Item[] */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindWorldLinksOfMedium);

        $query = '';
        if ($parameters->type === 'document') {
            $query = <<<'SQL'
                SELECT
                    id, type, name, short_description as shortDescription
                FROM world_items
                WHERE id IN (SELECT world_item_id FROM world_item_documents WHERE document_id = :id)
                ORDER BY name
            SQL;
        }

        if ($parameters->type === 'image') {
            $query = <<<'SQL'
                SELECT
                    id, type, name, short_description as shortDescription
                FROM world_items
                WHERE id IN (SELECT world_item_id FROM world_item_images WHERE image_id = :id)
                ORDER BY name
            SQL;
        }

        if ($parameters->type === 'conversation') {
            $query = <<<'SQL'
                SELECT
                    id, type, name, short_description as shortDescription
                FROM world_items
                WHERE id IN (SELECT world_item_id FROM world_item_conversations WHERE conversation_id = :id)
                ORDER BY name
            SQL;
        }

        return $this->denormalizer->denormalize(
            $this->platform->fetch($query, ['id' => $parameters->mediumId]),
            Item::class . '[]',
        );
    }
}
