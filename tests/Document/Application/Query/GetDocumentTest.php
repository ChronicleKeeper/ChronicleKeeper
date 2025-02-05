<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Application\Query\GetDocumentQuery;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(GetDocument::class)]
#[CoversClass(GetDocumentQuery::class)]
#[Large]
class GetDocumentTest extends DatabaseTestCase
{
    private GetDocumentQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(GetDocumentQuery::class);
        assert($query instanceof GetDocumentQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itHasTheCorrectQueryClass(): void
    {
        $query = new GetDocument('fef8517b-6ffe-4102-b4e5-5f685764f2be');

        self::assertSame(GetDocumentQuery::class, $query->getQueryClass());
        self::assertSame('fef8517b-6ffe-4102-b4e5-5f685764f2be', $query->id);
    }

    #[Test]
    public function itIsAbleToQueryTheDocument(): void
    {
        // ------------------- The test setup -------------------

        $document = (new DocumentBuilder())
            ->withId('23d99bcc-9c4f-43b8-872f-55bfed0db605')
            ->withTitle('Foo Bar Baz')
            ->build();

        $this->bus->dispatch(new StoreDocument($document));

        // ------------------- The test execution -------------------

        $document = $this->query->query(new GetDocument($document->getId()));

        // ------------------- The test assertions -------------------

        self::assertSame('23d99bcc-9c4f-43b8-872f-55bfed0db605', $document->getId());
        self::assertSame('Foo Bar Baz', $document->getTitle());
    }
}
