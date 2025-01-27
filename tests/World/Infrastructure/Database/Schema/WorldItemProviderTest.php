<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Infrastructure\Database\Schema;

use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use ChronicleKeeper\World\Infrastructure\Database\Schema\WorldItemProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(WorldItemProvider::class)]
#[Large]
class WorldItemProviderTest extends SchemaProviderTestCase
{
    #[Test]
    public function itCreatesTheSchema(): void
    {
        (new WorldItemProvider())->createSchema($this->databasePlatform);

        $tables = $this->schemaManager->getTables();

        self::assertCount(5, $tables);
        self::assertContains('world_items', $tables);
        self::assertContains('world_item_relations', $tables);
        self::assertContains('world_item_documents', $tables);
        self::assertContains('world_item_images', $tables);
        self::assertContains('world_item_conversations', $tables);
    }
}
