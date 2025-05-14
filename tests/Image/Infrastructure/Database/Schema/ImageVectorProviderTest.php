<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Infrastructure\Database\Schema;

use ChronicleKeeper\Image\Infrastructure\Database\Schema\ImageVectorProvider;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\VectorExtensionProvider;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ImageVectorProvider::class)]
#[Large]
class ImageVectorProviderTest extends SchemaProviderTestCase
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
        (new ImageVectorProvider())->createSchema($this->connection);

        $tables = $this->schemaManager->getTables();

        self::assertSame('images_vectors', $tables[0]); // The base table for embeddings
    }
}
