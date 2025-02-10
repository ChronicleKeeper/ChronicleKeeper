<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Infrastructure\Database\Schema;

use ChronicleKeeper\Image\Infrastructure\Database\Schema\ImageVectorProvider;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ImageVectorProvider::class)]
#[Large]
class ImageVectorProviderTest extends SchemaProviderTestCase
{
    #[Test]
    public function itCreatesTheSchema(): void
    {
        (new ImageVectorProvider())->createSchema($this->databasePlatform);

        $tables = $this->schemaManager->getTables();

        self::assertSame('images_vectors', $tables[0]); // The base table for embeddings
    }
}
