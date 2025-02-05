<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\DeleteDocument;
use ChronicleKeeper\Document\Application\Command\DeleteDocumentHandler;
use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Command\StoreDocumentVectors;
use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\VectorDocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(DeleteDocument::class)]
#[CoversClass(DeleteDocumentHandler::class)]
#[Large]
class DeleteDocumentTest extends DatabaseTestCase
{
    private DeleteDocumentHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(DeleteDocumentHandler::class);
        assert($handler instanceof DeleteDocumentHandler);

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
        $document = (new DocumentBuilder())->build();
        $command  = new DeleteDocument($document);

        self::assertSame($document, $command->document);
    }

    #[Test]
    public function itEnsuresTheDocumentAndItsVectoresAreDeleted(): void
    {
        // ------------------- The test setup -------------------
        $document = (new DocumentBuilder())
            ->withId('ae062666-e56e-4880-bf78-b45c98278ecf')
            ->withTitle('Test Document')
            ->withContent('This is a test document')
            ->build();

        $this->bus->dispatch(new StoreDocument($document));

        $vectorDocument = (new VectorDocumentBuilder())
            ->withDocument($document)
            ->build();

        $this->bus->dispatch(new StoreDocumentVectors($vectorDocument));

        // ------------------- The test execution -------------------

        $result = ($this->handler)(new DeleteDocument($document));

        // ------------------- The test assertions -------------------

        self::assertEquals([new DocumentDeleted($document)], $result->getEvents());

        $this->assertTableIsEmpty('documents');
        $this->assertTableIsEmpty('documents_vectors');
    }
}
