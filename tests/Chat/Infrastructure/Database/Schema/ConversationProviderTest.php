<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\Database\Schema;

use ChronicleKeeper\Chat\Infrastructure\Database\Schema\ConversationProvider;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ConversationProvider::class)]
#[Large]
class ConversationProviderTest extends SchemaProviderTestCase
{
    #[Test]
    public function itCreatesTheSchema(): void
    {
        (new ConversationProvider())->createSchema($this->connection);

        $tables = $this->schemaManager->getTables();

        self::assertCount(3, $tables);
        self::assertSame('conversations', $tables[0]);
        self::assertSame('conversation_settings', $tables[1]);
        self::assertSame('conversation_messages', $tables[2]);
    }
}
