<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Query;

use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Library\Application\Query\FindAllDirectories;
use ChronicleKeeper\Library\Application\Query\FindAllDirectoriesQuery;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindAllDirectories::class)]
#[CoversClass(FindAllDirectoriesQuery::class)]
#[Large]
final class FindAllDirectoriesTest extends DatabaseTestCase
{
    private FindAllDirectoriesQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindAllDirectoriesQuery::class);
        assert($query instanceof FindAllDirectoriesQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itHasACorrectQueryClassInUse(): void
    {
        self::assertSame(FindAllDirectoriesQuery::class, (new FindAllDirectories())->getQueryClass());
    }

    #[Test]
    public function itIsAbleToFetchAllDirectories(): void
    {
        // ------------------- The test setup -------------------

        $directory = (new DirectoryBuilder())->withTitle('foo')->build();
        $this->bus->dispatch(new StoreDirectory($directory));
        $directory = (new DirectoryBuilder())->withTitle('bar')->build();
        $this->bus->dispatch(new StoreDirectory($directory));
        $directory = (new DirectoryBuilder())->withParent($directory)->withTitle('aBaz')->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        // ------------------- The test execution -------------------

        $result = $this->query->query(new FindAllDirectories());

        // ------------------- The test assertions -------------------

        self::assertCount(4, $result);
        self::assertSame('Hauptverzeichnis', $result[0]->getTitle());
        self::assertSame('bar', $result[1]->getTitle());
        self::assertSame('aBaz', $result[2]->getTitle());
        self::assertSame('foo', $result[3]->getTitle());
    }
}
