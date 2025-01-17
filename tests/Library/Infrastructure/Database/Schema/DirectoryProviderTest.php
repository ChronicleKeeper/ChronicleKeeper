<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Infrastructure\Database\Schema;

use ChronicleKeeper\Library\Infrastructure\Database\Schema\DirectoryProvider;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DirectoryProvider::class)]
#[Large]
class DirectoryProviderTest extends SchemaProviderTestCase
{
    #[Test]
    public function itCreatesTheSchema(): void
    {
        (new DirectoryProvider())->createSchema($this->databasePlatform);

        $tables = $this->schemaManager->getTables();

        self::assertCount(1, $tables);
        self::assertSame('directories', $tables[0]);
    }
}
