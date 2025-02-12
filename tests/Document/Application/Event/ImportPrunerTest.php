<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Event;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Command\StoreDocumentVectors;
use ChronicleKeeper\Document\Application\Event\ImportPruner;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\VectorDocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(ImportPruner::class)]
#[Large]
class ImportPrunerTest extends DatabaseTestCase
{
    private ImportPruner $importPruner;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $importPruner = self::getContainer()->get(ImportPruner::class);
        assert($importPruner instanceof ImportPruner);

        $this->importPruner = $importPruner;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->importPruner);
    }

    #[Test]
    public function itIsPruning(): void
    {
        // ------------------- The test setup -------------------

        $document = (new DocumentBuilder())
            ->withId('b2176b89-e842-49a3-b6b7-ec1a55256d04')
            ->build();

        $this->bus->dispatch(new StoreDocument($document));

        $documentVector = (new VectorDocumentBuilder())
            ->withId('570a0490-6dd5-4363-8072-cfed97959e1b')
            ->build();

        $this->bus->dispatch(new StoreDocumentVectors($documentVector));

        // ------------------- The test scenario -------------------

        ($this->importPruner)(new ExecuteImportPruning(new ImportSettings()));

        // ------------------- The test assertions -------------------

        $this->assertTableIsEmpty('documents_vectors');
        $this->assertTableIsEmpty('documents');
    }
}
