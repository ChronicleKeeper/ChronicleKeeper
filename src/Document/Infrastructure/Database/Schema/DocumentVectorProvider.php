<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlDatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\DefaultSchemaProvider;

final class DocumentVectorProvider extends DefaultSchemaProvider
{
    public function createSchema(DatabasePlatform $platform): void
    {
        if ($platform instanceof PgSqlDatabasePlatform) {
            $platform->executeRaw(<<<'SQL'
                CREATE TABLE documents_vectors (
                    document_id uuid PRIMARY KEY,
                    embedding vector(1536) NOT NULL,
                    content text NOT NULL,
                    "vectorContentHash" text NOT NULL
                );
            SQL);

            return;
        }

        $platform->executeRaw(<<<'SQL'
            create virtual table documents_vectors using vec0(
                document_id text partition key,
                embedding float[1536] distance_metric=cosine,
                +content text,
                +vectorContentHash text
            );
        SQL);
    }
}
