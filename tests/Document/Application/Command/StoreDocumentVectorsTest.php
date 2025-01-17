<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\StoreDocumentVectors;
use ChronicleKeeper\Document\Application\Command\StoreDocumentVectorsHandler;
use ChronicleKeeper\Document\Domain\Entity\VectorDocument;
use ChronicleKeeper\Test\Document\Domain\Entity\VectorDocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function implode;

#[CoversClass(StoreDocumentVectors::class)]
#[CoversClass(StoreDocumentVectorsHandler::class)]
#[Small]
class StoreDocumentVectorsTest extends TestCase
{
    #[Test]
    public function commandIsInstantiatable(): void
    {
        $vectorDocument = self::createStub(VectorDocument::class);
        $command        = new StoreDocumentVectors($vectorDocument);

        self::assertSame($vectorDocument, $command->vectorDocument);
    }

    #[Test]
    public function documentIsStored(): void
    {
        $vectorDocument = (new VectorDocumentBuilder())->build();

        $databasePlatform = new DatabasePlatformMock();
        $handler          = new StoreDocumentVectorsHandler($databasePlatform);
        $handler(new StoreDocumentVectors($vectorDocument));

        $databasePlatform->assertExecutedInsert('documents_vectors', [
            'document_id' => $vectorDocument->document->getId(),
            'embedding' => '[' . implode(',', $vectorDocument->vector) . ']',
            'content' => $vectorDocument->content,
            'vectorContentHash' => $vectorDocument->vectorContentHash,
        ]);
    }
}
