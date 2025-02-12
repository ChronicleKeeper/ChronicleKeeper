<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\MissingResults;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\UnambiguousResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreItemRelationHandler
{
    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function __invoke(StoreItemRelation $itemRelation): void
    {
        $query = <<<'SQL'
            SELECT 1
            FROM world_item_relations
            WHERE
            (
                (source_world_item_id = :source_world_item_id AND target_world_item_id = :target_world_item_id)
                OR (source_world_item_id = :target_world_item_id AND target_world_item_id = :source_world_item_id)
            )
            AND relation_type = :relation_type
        SQL;

        try {
            // Check if the relation exists
            $this->platform->fetchOne($query, [
                'source_world_item_id' => $itemRelation->sourceItemId,
                'target_world_item_id' => $itemRelation->targetItemId,
                'relation_type' => $itemRelation->relationType,
            ]);
        } catch (UnambiguousResult) {
            // There is already a result
            return;
        } catch (MissingResults) {
            // Continue with the creation as no result exists, so everything is as expected
        }

        $this->platform->createQueryBuilder()->createInsert()
            ->insert('world_item_relations')
            ->onConflict(['source_world_item_id', 'target_world_item_id', 'relation_type'])
            ->values([
                'source_world_item_id' => $itemRelation->sourceItemId,
                'target_world_item_id' => $itemRelation->targetItemId,
                'relation_type' => $itemRelation->relationType,
            ])
            ->execute();
    }
}
