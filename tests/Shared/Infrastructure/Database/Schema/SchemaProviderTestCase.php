<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema;

use ChronicleKeeper\Test\WebTestCase;
use Override;

abstract class SchemaProviderTestCase extends WebTestCase
{
    #[Override]
    protected static function willSetupSchema(): bool
    {
        return false;
    }

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        // Ensure the Schema is empty
        $this->schemaManager->dropSchema();
    }
}
