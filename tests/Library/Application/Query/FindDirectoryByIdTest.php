<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Query;

use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Library\Application\Query\FindDirectoryById;
use ChronicleKeeper\Library\Application\Query\FindDirectoryByIdQuery;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindDirectoryById::class)]
#[CoversClass(FindDirectoryByIdQuery::class)]
#[Large]
final class FindDirectoryByIdTest extends DatabaseTestCase
{
    private FindDirectoryByIdQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindDirectoryByIdQuery::class);
        assert($query instanceof FindDirectoryByIdQuery);

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

        new FindDirectoryById('invalid-id');
    }

    #[Test]
    public function itHasACorrectQueryClassInUse(): void
    {
        self::assertSame(
            FindDirectoryByIdQuery::class,
            (new FindDirectoryById('d4031c41-fb69-402b-84d5-bab2f3614fc2'))->getQueryClass(),
        );
    }

    #[Test]
    public function itWillReturnTheRootDirectoryWhenSearchedForIt(): void
    {
        $result = $this->query->query(new FindDirectoryById(RootDirectory::ID));

        self::assertSame('Hauptverzeichnis', $result->getTitle());
    }

    #[Test]
    public function itIsAbleToFetchDirectoryById(): void
    {
        // ------------------- The test setup -------------------

        $directory = (new DirectoryBuilder())
            ->withId('d4031c41-fb69-402b-84d5-bab2f3614fc2')
            ->withTitle('foo')
            ->build();

        $this->bus->dispatch(new StoreDirectory($directory));

        // ------------------- The test execution -------------------

        $result = $this->query->query(new FindDirectoryById('d4031c41-fb69-402b-84d5-bab2f3614fc2'));

        // ------------------- The test assertions -------------------

        self::assertSame('foo', $result->getTitle());
    }
}
