<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Command\StoreDocumentHandler;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Domain\Event\DocumentRenamed;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoreDocument::class)]
#[CoversClass(StoreDocumentHandler::class)]
#[Small]
class StoreDocumentTest extends TestCase
{
    #[Test]
    public function commandIsInstantiatable(): void
    {
        $document = self::createStub(Document::class);
        $command  = new StoreDocument($document);

        self::assertSame($document, $command->document);
    }

    #[Test]
    public function documentIsStored(): void
    {
        $document = (new DocumentBuilder())->build();
        $handler  = new StoreDocumentHandler($databasePlatform = new DatabasePlatformMock());
        $handler(new StoreDocument($document));

        $databasePlatform->assertExecutedInsert('documents', [
            'id'          => $document->getId(),
            'title'       => $document->getTitle(),
            'content'     => $document->getContent(),
            'directory'   => $document->getDirectory()->getId(),
            'last_updated' => $document->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    #[Test]
    public function eventsAreReturned(): void
    {
        $document = (new DocumentBuilder())->build();
        $document->rename('new-name');

        $handler          = new StoreDocumentHandler(new DatabasePlatformMock());
        $dispatchedEvents = $handler(new StoreDocument($document))->getEvents();

        self::assertNotEmpty($dispatchedEvents);
        self::assertInstanceOf(DocumentRenamed::class, $dispatchedEvents[0]);
    }
}
