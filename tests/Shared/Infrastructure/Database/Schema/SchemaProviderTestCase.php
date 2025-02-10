<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\SchemaProvider;
use ChronicleKeeper\Test\WebTestCase;
use Override;

abstract class SchemaProviderTestCase extends WebTestCase
{
    #[Override]
    protected static function willSetupSchema(): bool
    {
        return false;
    }

    /** @return list<class-string<SchemaProvider>> */
    protected static function getRequiredSchemaProviders(): array
    {
        return [];
    }

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        // Ensure the Schema is empty
        $this->schemaManager->dropSchema();

        if (static::getRequiredSchemaProviders() === []) {
            return;
        }

        $this->schemaManager->createSchema(static::getRequiredSchemaProviders());
    }
}
