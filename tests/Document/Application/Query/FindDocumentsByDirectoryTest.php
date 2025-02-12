<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectoryQuery;
use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindDocumentsByDirectory::class)]
#[CoversClass(FindDocumentsByDirectoryQuery::class)]
#[Large]
class FindDocumentsByDirectoryTest extends DatabaseTestCase
{
    private FindDocumentsByDirectoryQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindDocumentsByDirectoryQuery::class);
        assert($query instanceof FindDocumentsByDirectoryQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itHasAConstructableQueryParametersClass(): void
    {
        $parameters = new FindDocumentsByDirectory('ffa8cf2d-e0ff-4777-a106-9353917d67c3');

        self::assertSame('ffa8cf2d-e0ff-4777-a106-9353917d67c3', $parameters->id);
        self::assertSame(FindDocumentsByDirectoryQuery::class, $parameters->getQueryClass());
    }

    #[Test]
    public function itIsWorkingWhenThereAreNoResults(): void
    {
        // ------------------- The test setup -------------------

        $directory = (new DirectoryBuilder())->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        // ------------------- The test execution -------------------

        $documents = $this->query->query(new FindDocumentsByDirectory($directory->getId()));

        self::assertSame([], $documents);
    }

    #[Test]
    public function itWillFindAllDocumentsThatAreWithingADirectory(): void
    {
        // ------------------- The test setup -------------------

        $directory = (new DirectoryBuilder())->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        $findableDocument = (new DocumentBuilder())
            ->withDirectory($directory)
            ->withTitle('foo')
            ->withContent('foo')
            ->build();

        $this->bus->dispatch(new StoreDocument($findableDocument));

        $unfindableDocument = (new DocumentBuilder())
            ->withTitle('bar')
            ->withContent('bar')
            ->build();

        $this->bus->dispatch(new StoreDocument($unfindableDocument));

        // ------------------- The test execution -------------------

        $documents = $this->query->query(new FindDocumentsByDirectory($directory->getId()));

        // ------------------- The test assertions -------------------

        self::assertCount(1, $documents);
        self::assertSame($findableDocument->getId(), $documents[0]->getId());
    }
}
