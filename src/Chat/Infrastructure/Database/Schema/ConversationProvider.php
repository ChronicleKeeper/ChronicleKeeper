<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class ConversationProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
        // TODO: Store a timestamp of the last update ... and use it to sort in FindLatestConversationsQuery
        $platform->executeRaw(<<<'SQL'
            CREATE TABLE conversations (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                directory TEXT NOT NULL
            );
        SQL);

        $platform->executeRaw(<<<'SQL'
            CREATE INDEX idx_conversation_directory
            ON conversations (directory);
        SQL);

        $platform->executeRaw(<<<'SQL'
            CREATE TABLE conversation_settings (
                conversation_id TEXT PRIMARY KEY,
                version TEXT NOT NULL,
                temperature REAL NOT NULL,
                images_max_distance FLOAT NOT NULL,
                documents_max_distance FLOAT NOT NULL,
                FOREIGN KEY(conversation_id) REFERENCES conversations(id)
            );
        SQL);

        $platform->executeRaw(<<<'SQL'
            CREATE INDEX idx_conversation_settings_conversation_id
            ON conversation_settings (conversation_id);
        SQL);

        $platform->executeRaw(<<<'SQL'
            CREATE TABLE conversation_messages (
                id TEXT PRIMARY KEY,
                conversation_id TEXT NOT NULL,
                role TEXT NOT NULL,
                content TEXT NOT NULL,
                context TEXT,
                debug TEXT,
                FOREIGN KEY(conversation_id) REFERENCES conversations(id)
            );
        SQL);

        $platform->executeRaw(<<<'SQL'
            CREATE INDEX idx_messages_conversation_id
            ON conversation_messages (conversation_id);
        SQL);
    }
}
