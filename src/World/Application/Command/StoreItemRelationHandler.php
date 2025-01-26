<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\World\Application\Command\Exception\WorldItemRelationAlreadyExists;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreItemRelationHandler
{
    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function __invoke(StoreItemRelation $itemRelation): void
    {
        $relationExists = $this->platform->fetchSingleRow(
            'SELECT 1 FROM world_item_relations WHERE (source_world_item_id = :sourceItemId AND target_world_item_id = :targetItemId) OR (source_world_item_id = :targetItemId AND target_world_item_id = :sourceItemId)',
            [
                'sourceItemId' => $itemRelation->sourceItemId,
                'targetItemId' => $itemRelation->targetItemId,
            ],
        );

        if ($relationExists !== null) {
            throw WorldItemRelationAlreadyExists::fromSourceAndTargetIds(
                $itemRelation->sourceItemId,
                $itemRelation->targetItemId,
            );
        }

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
