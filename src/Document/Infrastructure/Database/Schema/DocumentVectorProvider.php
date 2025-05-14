<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;
use Doctrine\DBAL\Connection;
use Override;

final class DocumentVectorProvider extends DefaultSchemaProvider
{
    #[Override]
    public function createSchema(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS documents_vectors (
                document_id uuid NOT NULL,
                embedding vector(1536) NOT NULL,
                content text NOT NULL,
                "vectorContentHash" text NOT NULL
            );
        SQL);
    }
}
