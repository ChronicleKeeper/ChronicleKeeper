<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;
use Doctrine\DBAL\Connection;
use Override;

final class WorldItemProvider extends DefaultSchemaProvider
{
    #[Override]
    public function createSchema(Connection $connection): void
    {
        // Create main world_items table
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS world_items (
                id TEXT PRIMARY KEY,
                type TEXT NOT NULL,
                name TEXT NOT NULL,
                short_description TEXT NOT NULL
            );
        SQL);

        // Create relation table between world items
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS world_item_relations (
                source_world_item_id TEXT NOT NULL,
                target_world_item_id TEXT NOT NULL,
                relation_type TEXT NOT NULL,
                PRIMARY KEY (source_world_item_id, target_world_item_id),
                FOREIGN KEY (source_world_item_id) REFERENCES world_items(id),
                FOREIGN KEY (target_world_item_id) REFERENCES world_items(id)
            );
        SQL);

        // Create junction table for documents
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS world_item_documents (
                world_item_id TEXT NOT NULL,
                document_id TEXT NOT NULL,
                PRIMARY KEY (world_item_id, document_id),
                FOREIGN KEY (world_item_id) REFERENCES world_items(id),
                FOREIGN KEY (document_id) REFERENCES documents(id)
            );
        SQL);

        // Create junction table for images
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS world_item_images (
                world_item_id TEXT NOT NULL,
                image_id TEXT NOT NULL,
                PRIMARY KEY (world_item_id, image_id),
                FOREIGN KEY (world_item_id) REFERENCES world_items(id),
                FOREIGN KEY (image_id) REFERENCES images(id)
            );
        SQL);

        // Create junction table for conversations
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS world_item_conversations (
                world_item_id TEXT NOT NULL,
                conversation_id TEXT NOT NULL,
                PRIMARY KEY (world_item_id, conversation_id),
                FOREIGN KEY (world_item_id) REFERENCES world_items(id),
                FOREIGN KEY (conversation_id) REFERENCES conversations(id)
            );
        SQL);
    }
}
