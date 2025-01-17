<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Infrastructure\Database\Schema;

use ChronicleKeeper\Document\Infrastructure\Database\Schema\DocumentVectorProvider;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DocumentVectorProvider::class)]
#[Large]
class DocumentVectorProviderTest extends SchemaProviderTestCase
{
    #[Test]
    public function itCreatesTheSchema(): void
    {
        (new DocumentVectorProvider())->createSchema($this->databasePlatform);

        $tables = $this->schemaManager->getTables();

        self::assertCount(6, $tables); // The vec0 extension will create a bunch of tables
        self::assertSame('documents_vectors', $tables[0]); // The base table for embeddings
    }
}
