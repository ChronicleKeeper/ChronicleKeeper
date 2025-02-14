<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\PgSql\QueryBuilder\Traits;

use ChronicleKeeper\Shared\Infrastructure\Database\PgSql\QueryBuilder\Traits\WhereClauseBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\PgSql\QueryBuilder\Traits\Stub\TestQueryBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversTrait(WhereClauseBuilder::class)]
#[Group('pgsql')]
#[Small]
class WhereClauseBuilderTest extends TestCase
{
    private TestQueryBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new TestQueryBuilder();
    }

    #[Test]
    public function simpleWhereCondition(): void
    {
        $this->builder->where('id', '=', 1);

        self::assertSame(' WHERE "id" = :id_1', $this->builder->getRenderedWhereClause());
        self::assertSame(['id_1' => 1], $this->builder->getParameters());
    }

    #[Test]
    public function simpleOrWhereCondition(): void
    {
        $this->builder->orWhere([['id', '=', 1], ['id', '=', 2]]);

        self::assertSame(' WHERE ("id" = :id_1 OR "id" = :id_2)', $this->builder->getRenderedWhereClause());
        self::assertSame(['id_1' => 1, 'id_2' => 2], $this->builder->getParameters());
    }

    #[Test]
    public function combinedAndAndOrConditions(): void
    {
        $this->builder
            ->where('id', '=', 1)
            ->orWhere([['id', '=', 2], ['id', '=', 3]]);

        self::assertSame(
            ' WHERE "id" = :id_1 AND ("id" = :id_2 OR "id" = :id_3)',
            $this->builder->getRenderedWhereClause(),
        );
        self::assertSame(['id_1' => 1, 'id_2' => 2, 'id_3' => 3], $this->builder->getParameters());
    }

    #[Test]
    public function multipleWhereConditions(): void
    {
        $this->builder
            ->where('id', '=', 1)
            ->where('active', '=', true);

        self::assertSame(' WHERE "id" = :id_1 AND "active" = :active_2', $this->builder->getRenderedWhereClause());
        self::assertEquals(['id_1' => 1, 'active_2' => true], $this->builder->getParameters());
    }

    #[Test]
    public function whereInCondition(): void
    {
        $this->builder->where('id', 'IN', [1, 2, 3]);

        self::assertSame(' WHERE "id" IN (:id_1_0, :id_1_1, :id_1_2)', $this->builder->getRenderedWhereClause());
        self::assertSame(['id_1_0' => 1, 'id_1_1' => 2, 'id_1_2' => 3], $this->builder->getParameters());
    }

    #[Test]
    public function whereNotInCondition(): void
    {
        $this->builder->where('id', 'NOT IN', [1, 2, 3]);

        self::assertSame(' WHERE "id" NOT IN (:id_1_0, :id_1_1, :id_1_2)', $this->builder->getRenderedWhereClause());
        self::assertSame(['id_1_0' => 1, 'id_1_1' => 2, 'id_1_2' => 3], $this->builder->getParameters());
    }

    #[Test]
    public function withAnInvalidOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operator: invalid');

        $this->builder->where('id', 'invalid', 1);
    }
}
