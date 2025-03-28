<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\QueryBuilder;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\SQLiteUpdateQueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SQLiteUpdateQueryBuilder::class)]
#[Group('sqlite')]
#[Small]
final class SQLiteUpdateQueryBuilderTest extends TestCase
{
    private DatabasePlatform&MockObject $databasePlatform;
    private SQLiteUpdateQueryBuilder $builder;

    protected function setUp(): void
    {
        $this->databasePlatform = $this->createMock(DatabasePlatform::class);
        $this->builder          = new SQLiteUpdateQueryBuilder($this->databasePlatform);
    }

    protected function tearDown(): void
    {
        unset($this->databasePlatform, $this->builder);
    }

    #[Test]
    public function itBuildsBasicUpdateQuery(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('query')
            ->with(
                'UPDATE users SET name = :name WHERE id = :id_1',
                ['name' => 'John Doe', 'id_1' => 1],
            );

        $this->builder
            ->update('users')
            ->set(['name' => 'John Doe'])
            ->where('id', '=', 1);

        $this->builder->execute();
    }

    #[Test]
    public function itUpdatesMultipleColumns(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('query')
            ->with(
                'UPDATE users SET name = :name, email = :email, active = :active WHERE id = :id_1',
                [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'active' => true,
                    'id_1' => 1,
                ],
            );

        $this->builder
            ->update('users')
            ->set([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'active' => true,
            ])
            ->where('id', '=', 1);

        $this->builder->execute();
    }

    #[Test]
    public function itHandlesNullValues(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('query')
            ->with(
                'UPDATE users SET deleted_at = :deleted_at, name = :name WHERE id = :id_1',
                [
                    'deleted_at' => null,
                    'name' => 'John Doe',
                    'id_1' => 1,
                ],
            );

        $this->builder
            ->update('users')
            ->set([
                'deleted_at' => null,
                'name' => 'John Doe',
            ])
            ->where('id', '=', 1);

        $this->builder->execute();
    }

    #[Test]
    public function itHandlesMultipleWhereConditions(): void
    {
        $this->databasePlatform
            ->expects($this->once())
            ->method('query')
            ->with(
                'UPDATE users SET status = :status WHERE last_login < :last_login_1 AND active = :active_2',
                [
                    'status' => 'inactive',
                    'last_login_1' => '2024-01-01',
                    'active_2' => true,
                ],
            );

        $this->builder
            ->update('users')
            ->set(['status' => 'inactive'])
            ->where('last_login', '<', '2024-01-01')
            ->where('active', '=', true);

        $this->builder->execute();
    }
}
