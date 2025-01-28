<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveItemRelationHandler
{
    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function __invoke(RemoveItemRelation $itemRelation): void
    {
        $this->platform->query(
            <<<'SQL'
                DELETE FROM
                       world_item_relations
                WHERE
                    (
                        (source_world_item_id = :source_world_item_id AND target_world_item_id = :target_world_item_id)
                        OR (source_world_item_id = :target_world_item_id AND target_world_item_id = :source_world_item_id)
                    )
                    AND relation_type = :relation_type
            SQL,
            [
                'source_world_item_id' => $itemRelation->sourceItemId,
                'target_world_item_id' => $itemRelation->targetItemId,
                'relation_type' => $itemRelation->relationType,
            ],
        );
    }
}
