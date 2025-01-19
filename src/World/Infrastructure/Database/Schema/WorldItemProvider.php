<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class WorldItemProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
        $platform->query(<<<'SQL'
            CREATE TABLE world_items (
                id TEXT PRIMARY KEY,
                type TEXT NOT NULL CHECK(type IN (
                    'country', 'region', 'location',
                    'organization', 'person', 'race',
                    'event', 'quest', 'campaign',
                    'object', 'other'
                )),
                name TEXT NOT NULL,
                short_description TEXT NOT NULL
            );
        SQL);

        // Create junction table for documents
        $platform->query(<<<'SQL'
        CREATE TABLE world_item_documents (
            world_item_id TEXT NOT NULL,
            document_id TEXT NOT NULL,
            PRIMARY KEY (world_item_id, document_id),
            FOREIGN KEY (world_item_id) REFERENCES world_items(id),
            FOREIGN KEY (document_id) REFERENCES documents(id)
            );
        SQL);

        // Create junction table for images
        $platform->query(<<<'SQL'
            CREATE TABLE world_item_images (
                world_item_id TEXT NOT NULL,
                image_id TEXT NOT NULL,
                PRIMARY KEY (world_item_id, image_id),
                FOREIGN KEY (world_item_id) REFERENCES world_items(id),
                FOREIGN KEY (image_id) REFERENCES images(id)
            );
        SQL);

        // Create junction table for conversations
        $platform->query(<<<'SQL'
            CREATE TABLE world_item_conversations (
                world_item_id TEXT NOT NULL,
                conversation_id TEXT NOT NULL,
                PRIMARY KEY (world_item_id, conversation_id),
                FOREIGN KEY (world_item_id) REFERENCES world_items(id),
                FOREIGN KEY (conversation_id) REFERENCES conversations(id)
            );
        SQL);
    }
}
