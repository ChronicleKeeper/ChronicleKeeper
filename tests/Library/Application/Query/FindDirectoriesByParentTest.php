<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Query;

use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Library\Application\Query\FindDirectoriesByParent;
use ChronicleKeeper\Library\Application\Query\FindDirectoriesByParentQuery;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindDirectoriesByParent::class)]
#[CoversClass(FindDirectoriesByParentQuery::class)]
#[Large]
final class FindDirectoriesByParentTest extends DatabaseTestCase
{
    private FindDirectoriesByParentQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindDirectoriesByParentQuery::class);
        assert($query instanceof FindDirectoriesByParentQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itIsOnlyAcceptingCorrectIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "invalid-id" is not a valid UUID.');

        new FindDirectoriesByParent('invalid-id');
    }

    #[Test]
    public function itHasACorrectQueryClassInUse(): void
    {
        self::assertSame(
            FindDirectoriesByParentQuery::class,
            (new FindDirectoriesByParent('d4031c41-fb69-402b-84d5-bab2f3614fc2'))->getQueryClass(),
        );
    }

    #[Test]
    public function itIsAbleToFetchAllDirectoriesByParent(): void
    {
        // ------------------- The test setup -------------------

        $parentDirectory = (new DirectoryBuilder())->withTitle('foo')->build();
        $this->bus->dispatch(new StoreDirectory($parentDirectory));
        $directory = (new DirectoryBuilder())->withParent($parentDirectory)->withTitle('bar')->build();
        $this->bus->dispatch(new StoreDirectory($directory));
        $directory = (new DirectoryBuilder())->withParent($parentDirectory)->withTitle('aBaz')->build();
        $this->bus->dispatch(new StoreDirectory($directory));
        $directory = (new DirectoryBuilder())->withParent($directory)->withTitle('aBaz')->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        // ------------------- The test execution -------------------

        $result = $this->query->query(new FindDirectoriesByParent($parentDirectory->getId()));

        // ------------------- The test assertions -------------------

        self::assertCount(2, $result);
        self::assertSame('aBaz', $result[0]->getTitle());
        self::assertSame('bar', $result[1]->getTitle());
    }
}
