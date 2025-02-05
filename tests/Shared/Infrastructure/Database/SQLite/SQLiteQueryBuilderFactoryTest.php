<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteDeleteQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteInsertQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteSelectQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteUpdateQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\SQLiteQueryBuilderFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SQLiteQueryBuilderFactory::class)]
#[Small]
final class SQLiteQueryBuilderFactoryTest extends TestCase
{
    private SQLiteQueryBuilderFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new SQLiteQueryBuilderFactory(self::createStub(DatabasePlatform::class));
    }

    #[Test]
    public function itCreatesDeleteQueryBuilder(): void
    {
        $builder = $this->factory->createDelete();

        self::assertInstanceOf(SQLiteDeleteQueryBuilder::class, $builder);
    }

    #[Test]
    public function itCreatesInsertQueryBuilder(): void
    {
        $builder = $this->factory->createInsert();

        self::assertInstanceOf(SQLiteInsertQueryBuilder::class, $builder);
    }

    #[Test]
    public function itCreatesSelectQueryBuilder(): void
    {
        $builder = $this->factory->createSelect();

        self::assertInstanceOf(SQLiteSelectQueryBuilder::class, $builder);
    }

    #[Test]
    public function itCreatesUpdateQueryBuilder(): void
    {
        $builder = $this->factory->createUpdate();

        self::assertInstanceOf(SQLiteUpdateQueryBuilder::class, $builder);
    }
}
