<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Command\StoreDocumentHandler;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\ClockInterface;
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
            $clock      = $this->createMock(ClockInterface::class),
        );

        $fileAccess->expects($this->once())
            ->method('write')
            ->with(
                'library.documents',
                $document->id . '.json',
                $serializer->serialize($document, 'json'),
            );

        $clock->expects($this->once())
            ->method('now')
            ->willReturn($targetDateTime = new DateTimeImmutable());

        $handler(new StoreDocument($document));

        self::assertSame($targetDateTime, $document->updatedAt);
    }

    #[Test]
    public function documentIsStoredWithoutUpdatedTimestamp(): void
    {
        $document       = (new DocumentBuilder())->build();
        $handler        = new StoreDocumentHandler(
            $fileAccess = $this->createMock(FileAccess::class),
            $serializer = self::createStub(SerializerInterface::class),
            $clock      = $this->createMock(ClockInterface::class),
        );

        $fileAccess->expects($this->once())
            ->method('write')
            ->with(
                'library.documents',
                $document->id . '.json',
                $serializer->serialize($document, 'json'),
            );

        $clock->expects($this->never())->method('now');

        $targetDateTime = $document->updatedAt;
        $handler(new StoreDocument($document, false));

        self::assertSame($targetDateTime, $document->updatedAt);
    }
}
