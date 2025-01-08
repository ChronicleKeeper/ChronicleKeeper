<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\DeleteDocument;
use ChronicleKeeper\Document\Application\Command\DeleteDocumentHandler;
use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectors;
use ChronicleKeeper\Document\Application\Query\FindVectorsOfDocument;
use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\VectorDocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

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

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(DeleteDocumentVectors::class))
            ->willReturnCallback(static fn (DeleteDocumentVectors $command) => new Envelope($command));

        $databasePlatform = new DatabasePlatformMock();

        $handler = new DeleteDocumentHandler($bus, $queryService, $databasePlatform);
        $result  = $handler(new DeleteDocument($document));

        $databasePlatform->assertExecutedQuery('DELETE FROM documents WHERE id = :id', ['id' => $document->getId()]);

        self::assertEquals([new DocumentDeleted($document)], $result->getEvents());
    }
}
