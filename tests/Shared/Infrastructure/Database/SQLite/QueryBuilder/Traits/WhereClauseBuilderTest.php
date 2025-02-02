<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\QueryBuilder\Traits;

use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\Traits\WhereClauseBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\QueryBuilder\Traits\Stub\TestQueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(WhereClauseBuilder::class)]
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

        self::assertSame(' WHERE id = :id_1', $this->builder->getRenderedWhereClause());
        self::assertSame(['id_1' => 1], $this->builder->getParameters());
    }

    #[Test]
    public function multipleWhereConditions(): void
    {
        $this->builder
            ->where('id', '=', 1)
            ->where('active', '=', true);

        self::assertSame(' WHERE id = :id_1 AND active = :active_2', $this->builder->getRenderedWhereClause());
        self::assertEquals(['id_1' => 1, 'active_2' => true], $this->builder->getParameters());
    }

    #[Test]
    public function whereInCondition(): void
    {
        $this->builder->where('id', 'IN', [1, 2, 3]);

        self::assertSame(' WHERE id IN (:id_1_0,:id_1_1,:id_1_2)', $this->builder->getRenderedWhereClause());
        self::assertSame(['id_1_0' => 1, 'id_1_1' => 2, 'id_1_2' => 3], $this->builder->getParameters());
    }
}
