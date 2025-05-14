<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Infrastructure\Database\Schema;

use ChronicleKeeper\Document\Infrastructure\Database\Schema\DocumentVectorProvider;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\VectorExtensionProvider;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DocumentVectorProvider::class)]
#[Large]
class DocumentVectorProviderTest extends SchemaProviderTestCase
{
    /** @inheritDoc */
    #[Override]
    protected static function getRequiredSchemaProviders(): array
    {
        return [VectorExtensionProvider::class];
    }

    #[Test]
    public function itCreatesTheSchema(): void
    {
        (new DocumentVectorProvider())->createSchema($this->connection);

        $tables = $this->schemaManager->getTables();

        self::assertSame('documents_vectors', $tables[0]); // The base table for embeddings
    }
}
