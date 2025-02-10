<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Query\FindAllDocuments;
use ChronicleKeeper\Document\Application\Query\FindAllDocumentsQuery;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindAllDocuments::class)]
#[CoversClass(FindAllDocumentsQuery::class)]
#[Large]
class FindAllDocumentsTest extends DatabaseTestCase
{
    private FindAllDocumentsQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindAllDocumentsQuery::class);
        assert($query instanceof FindAllDocumentsQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itIsSupportingTheCorrectQueryClass(): void
    {
        self::assertSame(FindAllDocumentsQuery::class, (new FindAllDocuments())->getQueryClass());
    }

    #[Test]
    public function itIsNotFailingIfDatabaseIsEmpty(): void
    {
        $documents = $this->query->query(new FindAllDocuments());

        self::assertSame([], $documents);
        $this->assertTableIsEmpty('documents');
    }

    #[Test]
    public function isIsReturningSortedResults(): void
    {
        // ------------------- The test setup -------------------

        $aDocument = (new DocumentBuilder())
            ->withTitle('foo')
            ->withContent('foo')
            ->build();

        $this->bus->dispatch(new StoreDocument($aDocument));

        $bDocument = (new DocumentBuilder())
            ->withTitle('bar')
            ->withContent('bar')
            ->build();

        $this->bus->dispatch(new StoreDocument($bDocument));

        // ------------------- The test execution -------------------

        $documents = $this->query->query(new FindAllDocuments());

        // ------------------- The test assertions -------------------

        self::assertCount(2, $documents);
        self::assertSame('bar', $documents[0]->getTitle());
        self::assertSame('foo', $documents[1]->getTitle());
    }
}
