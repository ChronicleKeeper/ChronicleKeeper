<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Infrastructure\Database\Schema;

use ChronicleKeeper\Favorizer\Infrastructure\Database\Schema\FavoritesProvider;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FavoritesProvider::class)]
#[Large]
class FavoritesProviderTest extends SchemaProviderTestCase
{
    #[Test]
    public function itCreatesTheSchema(): void
    {
        (new FavoritesProvider())->createSchema($this->databasePlatform);

        $tables = $this->schemaManager->getTables();

        self::assertCount(1, $tables);
        self::assertSame('favorites', $tables[0]);
    }
}
