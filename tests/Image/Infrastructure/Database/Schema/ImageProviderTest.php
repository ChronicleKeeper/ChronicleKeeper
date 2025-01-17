<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Infrastructure\Database\Schema;

use ChronicleKeeper\Image\Infrastructure\Database\Schema\ImageProvider;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ImageProvider::class)]
#[Large]
class ImageProviderTest extends SchemaProviderTestCase
{
    #[Test]
    public function itCreatesTheSchema(): void
    {
        (new ImageProvider())->createSchema($this->databasePlatform);

        $tables = $this->schemaManager->getTables();

        self::assertCount(1, $tables);
        self::assertSame('images', $tables[0]);
    }
}
