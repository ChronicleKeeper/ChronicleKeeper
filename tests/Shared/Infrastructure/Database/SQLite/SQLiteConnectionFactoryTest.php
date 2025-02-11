<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite;

use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\SQLiteConnectionFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SQLiteConnectionFactory::class)]
#[Group('sqlite')]
final class SQLiteConnectionFactoryTest extends TestCase
{
    #[Test]
    public function itIsAbleToCreateAConnection(): void
    {
        $factory    = new SQLiteConnectionFactory(':memory:', __DIR__ . '/../../../../../');
        $connection = $factory->create();

        self::assertSame(1, $connection->querySingle('SELECT 1'));
    }
}
