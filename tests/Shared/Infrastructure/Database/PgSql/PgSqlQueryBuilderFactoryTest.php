<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\PgSql;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\PgSqlQueryBuilderFactory;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\PgSqlDeleteQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\PgSqlInsertQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\PgSqlSelectQueryBuilder;
use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\PgSqlUpdateQueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PgSqlQueryBuilderFactory::class)]
#[Group('pgsql')]
#[Small]
final class PgSqlQueryBuilderFactoryTest extends TestCase
{
    private PgSqlQueryBuilderFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new PgSqlQueryBuilderFactory(self::createStub(DatabasePlatform::class));
    }

    #[Test]
    public function itCreatesDeleteQueryBuilder(): void
    {
        $builder = $this->factory->createDelete();

        self::assertInstanceOf(PgSqlDeleteQueryBuilder::class, $builder);
    }

    #[Test]
    public function itCreatesInsertQueryBuilder(): void
    {
        $builder = $this->factory->createInsert();

        self::assertInstanceOf(PgSqlInsertQueryBuilder::class, $builder);
    }

    #[Test]
    public function itCreatesSelectQueryBuilder(): void
    {
        $builder = $this->factory->createSelect();

        self::assertInstanceOf(PgSqlSelectQueryBuilder::class, $builder);
    }

    #[Test]
    public function itCreatesUpdateQueryBuilder(): void
    {
        $builder = $this->factory->createUpdate();

        self::assertInstanceOf(PgSqlUpdateQueryBuilder::class, $builder);
    }
}
