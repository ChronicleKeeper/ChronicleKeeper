<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\DeleteDocument;
use ChronicleKeeper\Document\Application\Command\DeleteDocumentHandler;
use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectors;
use ChronicleKeeper\Library\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorDocumentRepository;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorDocument;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Library\Domain\Entity\DocumentBuilder;
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
        $command = new DeleteDocument('document-id');

        self::assertSame('document-id', $command->id);
    }

    #[Test]
    public function documentIsFullyDeleted(): void
    {
        $document       = (new DocumentBuilder())->build();
        $vectorDocument = new VectorDocument($document, 'content', 'foo', []);

        $vectorRepository = $this->createMock(FilesystemVectorDocumentRepository::class);
        $vectorRepository->expects($this->once())
            ->method('findAllByDocumentId')
            ->with($document->id)
            ->willReturn([$vectorDocument]);

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('delete')
            ->with('library.documents', $document->id . '.json');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(DeleteDocumentVectors::class))
            ->willReturnCallback(static fn (DeleteDocumentVectors $command) => new Envelope($command));

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(DocumentDeleted::class));

        $handler = new DeleteDocumentHandler($fileAccess, $eventDispatcher, $vectorRepository, $bus);
        $handler(new DeleteDocument($document->id));
    }
}
