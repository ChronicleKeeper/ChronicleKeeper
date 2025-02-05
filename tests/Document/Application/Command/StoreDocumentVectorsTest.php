<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\StoreDocumentVectors;
use ChronicleKeeper\Document\Application\Command\StoreDocumentVectorsHandler;
use ChronicleKeeper\Test\Document\Domain\Entity\VectorDocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(StoreDocumentVectors::class)]
#[CoversClass(StoreDocumentVectorsHandler::class)]
#[Large]
class StoreDocumentVectorsTest extends DatabaseTestCase
{
    private StoreDocumentVectorsHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(StoreDocumentVectorsHandler::class);
        assert($handler instanceof StoreDocumentVectorsHandler);

        $this->handler = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handler);
    }

    #[Test]
    public function itHasACommandThatIsConstructable(): void
    {
        $command = new StoreDocumentVectors($vectorDocument = (new VectorDocumentBuilder())->build());

        self::assertSame($vectorDocument, $command->vectorDocument);
    }

    #[Test]
    public function itCanStoreTheVectorDocument(): void
    {
        // ------------------- The test setup -------------------

        $vectorDocument = (new VectorDocumentBuilder())
            ->withId('15a20288-5e93-4895-897c-b158db2393e4')
            ->build();

        // ------------------- The test execution -------------------

        ($this->handler)(new StoreDocumentVectors($vectorDocument));

        // ------------------- The test assertions -------------------

        $this->assertRowsInTable('documents_vectors', 1);

        $vectors = $this->getRowFromTable(
            'documents_vectors',
            'document_id',
            $vectorDocument->document->getId(),
        );

        self::assertNotNull($vectors);
        self::assertSame($vectorDocument->document->getId(), $vectors['document_id']);
        self::assertSame($vectorDocument->content, $vectors['content']);
    }
}
