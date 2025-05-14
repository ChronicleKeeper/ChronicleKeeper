<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Infrastructure\Database\Schema;

use ChronicleKeeper\Chat\Infrastructure\Database\Schema\ConversationProvider;
use ChronicleKeeper\Document\Infrastructure\Database\Schema\DocumentProvider;
use ChronicleKeeper\Image\Infrastructure\Database\Schema\ImageProvider;
use ChronicleKeeper\Library\Infrastructure\Database\Schema\DirectoryProvider;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use ChronicleKeeper\World\Infrastructure\Database\Schema\WorldItemProvider;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(WorldItemProvider::class)]
#[Large]
final class WorldItemProviderTest extends SchemaProviderTestCase
{
    /** @inheritDoc */
    #[Override]
    protected static function getRequiredSchemaProviders(): array
    {
        return [
            DirectoryProvider::class,
            DocumentProvider::class,
            ImageProvider::class,
            ConversationProvider::class,
        ];
    }

    #[Test]
    public function itCreatesTheSchema(): void
    {
        (new WorldItemProvider())->createSchema($this->connection);

        $tables = $this->schemaManager->getTables();

        self::assertContains('world_items', $tables);
        self::assertContains('world_item_relations', $tables);
        self::assertContains('world_item_documents', $tables);
        self::assertContains('world_item_images', $tables);
        self::assertContains('world_item_conversations', $tables);
    }
}
