<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Command\StoreDocumentHandler;
use ChronicleKeeper\Document\Domain\Event\DocumentRenamed;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(StoreDocument::class)]
#[CoversClass(StoreDocumentHandler::class)]
#[Large]
class StoreDocumentTest extends DatabaseTestCase
{
    private StoreDocumentHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(StoreDocumentHandler::class);
        assert($handler instanceof StoreDocumentHandler);

        $this->handler = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handler);
    }

    #[Test]
    public function itHasAConstructableCommand(): void
    {
        $command = new StoreDocument($document = (new DocumentBuilder())->build());

        self::assertSame($document, $command->document);
    }

    #[Test]
    public function itEnsuresANonExistentDocumentIsStored(): void
    {
        // ------------------- The test setup -------------------

        $document = (new DocumentBuilder())
            ->withId('a89161c0-685c-44de-8bba-09ec863eadf1')
            ->build();

        // ------------------- The test execution -------------------

        $result = ($this->handler)(new StoreDocument($document));

        // ------------------- The test assertions -------------------

        $events = $result->getEvents();
        self::assertCount(0, $events); // No events as created with builder

        $this->assertRowsInTable('documents', 1);
    }

    #[Test]
    public function itEnsuresThatAChangedDocumentCanBeStored(): void
    {
        // ------------------- The test setup -------------------

        $document = (new DocumentBuilder())
            ->withId('a89161c0-685c-44de-8bba-09ec863eadf1')
            ->build();

        $this->bus->dispatch(new StoreDocument($document));

        // ------------------- The test execution -------------------

        $document->rename('new-name');

        $result = ($this->handler)(new StoreDocument($document));

        // ------------------- The test assertions -------------------

        $events = $result->getEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(DocumentRenamed::class, $events[0]);

        $rawDocument = $this->getRowFromTable(
            'documents',
            'id',
            'a89161c0-685c-44de-8bba-09ec863eadf1',
        );

        self::assertNotNull($rawDocument);
        self::assertSame('new-name', $rawDocument['title']);
    }
}
