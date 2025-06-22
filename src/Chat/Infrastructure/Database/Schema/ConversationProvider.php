<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;
use Doctrine\DBAL\Connection;
use Override;

final class ConversationProvider extends DefaultSchemaProvider
{
    #[Override]
    public function createSchema(Connection $connection): void
    {
        // Create conversations table
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS conversations (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                directory TEXT NOT NULL
            );
        SQL);

        // Create index on directory for faster lookups
        $connection->executeStatement(<<<'SQL'
            CREATE INDEX IF NOT EXISTS idx_conversation_directory
            ON conversations (directory);
        SQL);

        // Create conversation_settings table
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS conversation_settings (
                conversation_id TEXT PRIMARY KEY,
                version TEXT NOT NULL,
                temperature FLOAT NOT NULL,
                images_max_distance FLOAT NOT NULL,
                documents_max_distance FLOAT NOT NULL,
                FOREIGN KEY(conversation_id) REFERENCES conversations(id)
            );
        SQL);

        // Create index on conversation_id for faster lookups
        $connection->executeStatement(<<<'SQL'
            CREATE INDEX IF NOT EXISTS idx_conversation_settings_conversation_id
            ON conversation_settings (conversation_id);
        SQL);

        // Create conversation_messages table
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS conversation_messages (
                id TEXT PRIMARY KEY,
                conversation_id TEXT NOT NULL,
                role TEXT NOT NULL,
                content TEXT NOT NULL,
                context TEXT,
                debug TEXT,
                FOREIGN KEY(conversation_id) REFERENCES conversations(id)
            );
        SQL);

        // Create index on conversation_id for faster lookups
        $connection->executeStatement(<<<'SQL'
            CREATE INDEX IF NOT EXISTS idx_messages_conversation_id
            ON conversation_messages (conversation_id);
        SQL);
    }
}
