<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreItemRelationHandler
{
    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function __invoke(StoreItemRelation $itemRelation): void
    {
        $this->platform->insertOrUpdate(
            'world_item_relations',
            [
                'source_world_item_id' => $itemRelation->sourceItemId,
                'target_world_item_id' => $itemRelation->targetItemId,
                'relation_type' => $itemRelation->relationType,
            ],
        );
    }
}
