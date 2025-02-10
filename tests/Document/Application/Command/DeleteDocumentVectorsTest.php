<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectors;
use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectorsHandler;
use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(DeleteDocumentVectors::class)]
#[CoversClass(DeleteDocumentVectorsHandler::class)]
#[Large]
class DeleteDocumentVectorsTest extends DatabaseTestCase
{
    private DeleteDocumentVectorsHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(DeleteDocumentVectorsHandler::class);
        assert($handler instanceof DeleteDocumentVectorsHandler);

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
        $command = new DeleteDocumentVectors('9ad55522-e945-4b71-a1c7-3c3f2890a134');

        self::assertSame('9ad55522-e945-4b71-a1c7-3c3f2890a134', $command->documentId);
    }

    #[Test]
    public function itEnsuresThatTheDocumentIsDeletedFromDatabase(): void
    {
        // ------------------- The test setup -------------------

        $document = (new DocumentBuilder())
            ->withId('cfebff5b-2219-46f0-8960-9dec244b6d87')
            ->withTitle('Test Document')
            ->withContent('This is a test document')
            ->build();

        $this->bus->dispatch(new StoreDocument($document));

        // ------------------- The test execution -------------------

        ($this->handler)(new DeleteDocumentVectors('cfebff5b-2219-46f0-8960-9dec244b6d87'));

        // ------------------- The test assertions -------------------

        $this->assertTableIsEmpty('documents_vectors');
    }
}
