<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Infrastructure\Database\Schema;

use ChronicleKeeper\Document\Infrastructure\Database\Schema\DocumentProvider;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DocumentProvider::class)]
#[Large]
class DocumentProviderTest extends SchemaProviderTestCase
{
    #[Test]
    public function itCreatesTheSchema(): void
    {
        (new DocumentProvider())->createSchema($this->databasePlatform);

        $tables = $this->schemaManager->getTables();

        self::assertCount(1, $tables);
        self::assertSame('documents', $tables[0]);
    }
}
