<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Infrastructure\Database\Schema;

use ChronicleKeeper\ImageGenerator\Infrastructure\Database\Schema\GeneratorResultProvider;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\Schema\SchemaProviderTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(GeneratorResultProvider::class)]
#[Large]
class GeneratorResultProviderTest extends SchemaProviderTestCase
{
    #[Test]
    public function itCreatesTheSchema(): void
    {
        (new GeneratorResultProvider())->createSchema($this->databasePlatform);

        $tables = $this->schemaManager->getTables();

        self::assertCount(1, $tables);
        self::assertSame('generator_results', $tables[0]);
    }
}
