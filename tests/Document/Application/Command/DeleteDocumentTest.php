<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\DeleteDocument;
use ChronicleKeeper\Document\Application\Command\DeleteDocumentHandler;
use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectors;
use ChronicleKeeper\Document\Application\Query\FindVectorsOfDocument;
use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\VectorDocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[CoversClass(DeleteDocument::class)]
#[CoversClass(DeleteDocumentHandler::class)]
#[Small]
class DeleteDocumentTest extends TestCase
{
    #[Test]
    public function commandIsInstantiatable(): void
    {
        $document = (new DocumentBuilder())->build();
        $command  = new DeleteDocument($document);

        self::assertSame($document, $command->document);
    }

    #[Test]
    public function documentIsFullyDeleted(): void
    {
        $document       = (new DocumentBuilder())->build();
        $vectorDocument = (new VectorDocumentBuilder())->withDocument($document)->build();

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::isInstanceOf(FindVectorsOfDocument::class))
            ->willReturn([$vectorDocument]);

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('delete')
            ->with('library.documents', $document->getId() . '.json');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(DeleteDocumentVectors::class))
            ->willReturnCallback(static fn (DeleteDocumentVectors $command) => new Envelope($command));

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(DocumentDeleted::class));

        $handler = new DeleteDocumentHandler($fileAccess, $eventDispatcher, $bus, $queryService);
        $handler(new DeleteDocument($document));
    }
}
