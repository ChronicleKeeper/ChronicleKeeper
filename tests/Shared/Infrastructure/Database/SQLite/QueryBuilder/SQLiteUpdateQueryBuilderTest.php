<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteUpdateQueryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SQLiteUpdateQueryBuilder::class)]
#[Small]
final class SQLiteUpdateQueryBuilderTest extends TestCase
{
    private DatabasePlatformMock $databasePlatform;
    private SQLiteUpdateQueryBuilder $builder;

    protected function setUp(): void
    {
        $this->databasePlatform = new DatabasePlatformMock();
        $this->builder          = new SQLiteUpdateQueryBuilder($this->databasePlatform);
    }

    #[Test]
    public function itBuildsBasicUpdateQuery(): void
    {
        $this->builder
            ->update('users')
            ->set(['name' => 'John Doe'])
            ->where('id', '=', 1);

        $this->builder->execute();

        $this->databasePlatform->assertExecutedQuery(
            'UPDATE users SET name = :name WHERE id = :id_1',
            ['name' => 'John Doe', 'id_1' => 1],
        );
    }

    #[Test]
    public function itUpdatesMultipleColumns(): void
    {
        $this->builder
            ->update('users')
            ->set([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'active' => true,
            ])
            ->where('id', '=', 1);

        $this->builder->execute();

        $this->databasePlatform->assertExecutedQuery(
            'UPDATE users SET name = :name, email = :email, active = :active WHERE id = :id_3',
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'active' => true,
                'id_3' => 1,
            ],
        );
    }

    #[Test]
    public function itHandlesNullValues(): void
    {
        $this->builder
            ->update('users')
            ->set([
                'deleted_at' => null,
                'name' => 'John Doe',
            ])
            ->where('id', '=', 1);

        $this->builder->execute();

        $this->databasePlatform->assertExecutedQuery(
            'UPDATE users SET deleted_at = :deleted_at, name = :name WHERE id = :id_2',
            [
                'deleted_at' => null,
                'name' => 'John Doe',
                'id_2' => 1,
            ],
        );
    }

    #[Test]
    public function itHandlesMultipleWhereConditions(): void
    {
        $this->builder
            ->update('users')
            ->set(['status' => 'inactive'])
            ->where('last_login', '<', '2024-01-01')
            ->where('active', '=', true);

        $this->builder->execute();

        $this->databasePlatform->assertExecutedQuery(
            'UPDATE users SET status = :status WHERE last_login < :last_login_1 AND active = :active_2',
            [
                'status' => 'inactive',
                'last_login_1' => '2024-01-01',
                'active_2' => true,
            ],
        );
    }
}
