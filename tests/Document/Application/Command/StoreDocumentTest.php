<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Command\StoreDocumentHandler;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Domain\Event\DocumentRenamed;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

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
        $document       = (new DocumentBuilder())->build();
        $handler        = new StoreDocumentHandler(
            $fileAccess = $this->createMock(FileAccess::class),
            $serializer = self::createStub(SerializerInterface::class),
        );

        $fileAccess->expects($this->once())
            ->method('write')
            ->with(
                'library.documents',
                $document->getId() . '.json',
                $serializer->serialize($document, 'json'),
            );

        $handler(new StoreDocument($document));
    }

    #[Test]
    public function eventsAreReturned(): void
    {
        $document = (new DocumentBuilder())->build();
        $document->rename('new-name');

        $handler = new StoreDocumentHandler(
            self::createStub(FileAccess::class),
            self::createStub(SerializerInterface::class),
        );

        $dispatchedEvents = $handler(new StoreDocument($document))->getEvents();

        self::assertNotEmpty($dispatchedEvents);
        self::assertInstanceOf(DocumentRenamed::class, $dispatchedEvents[0]);
    }
}
