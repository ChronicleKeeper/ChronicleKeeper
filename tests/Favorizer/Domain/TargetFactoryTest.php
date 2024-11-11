<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Domain;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Favorizer\Domain\Exception\UnknownMedium;
use ChronicleKeeper\Favorizer\Domain\TargetFactory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDocumentRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\ImageBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TargetFactory::class)]
#[Small]
class TargetFactoryTest extends TestCase
{
    #[Test]
    public function createDocumentTarget(): void
    {
        $document = (new DocumentBuilder())
            ->withTitle('Foo Bar Baz Quoz Quux Corge Grault Garply Waldo Fred Plugh Xyzzy Thud')
            ->build();

        $filesystemDocumentRepository = $this->createMock(FilesystemDocumentRepository::class);
        $filesystemDocumentRepository->expects($this->once())
            ->method('findById')
            ->with($document->id)
            ->willReturn($document);

        $filesystemImageRepository = $this->createMock(FilesystemImageRepository::class);
        $filesystemImageRepository->expects($this->never())->method('findById');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $targetFactory = new TargetFactory($filesystemDocumentRepository, $filesystemImageRepository, $queryService);
        $target        = $targetFactory->create($document->id, $document::class);

        self::assertSame($document->id, $target->getId());
        self::assertSame('Foo Bar Baz Quoz Qu…', $target->getTitle());
    }

    #[Test]
    public function createImageTarget(): void
    {
        $image = (new ImageBuilder())
            ->withTitle('Foo Bar Baz Quoz Quux Corge Grault Garply Waldo Fred Plugh Xyzzy Thud')
            ->build();

        $filesystemDocumentRepository = $this->createMock(FilesystemDocumentRepository::class);
        $filesystemDocumentRepository->expects($this->never())->method('findById');

        $filesystemImageRepository = $this->createMock(FilesystemImageRepository::class);
        $filesystemImageRepository->expects($this->once())
            ->method('findById')
            ->with($image->id)
            ->willReturn($image);

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $targetFactory = new TargetFactory($filesystemDocumentRepository, $filesystemImageRepository, $queryService);
        $target        = $targetFactory->create($image->id, $image::class);

        self::assertSame($image->id, $target->getId());
        self::assertSame('Foo Bar Baz Quoz Qu…', $target->getTitle());
    }

    #[Test]
    public function createConversationTarget(): void
    {
        $conversation = (new ConversationBuilder())
            ->withTitle('Foo Bar Baz Quoz Quux Corge Grault Garply Waldo Fred Plugh Xyzzy Thud')
            ->build();

        $filesystemDocumentRepository = $this->createMock(FilesystemDocumentRepository::class);
        $filesystemDocumentRepository->expects($this->never())->method('findById');

        $filesystemImageRepository = $this->createMock(FilesystemImageRepository::class);
        $filesystemImageRepository->expects($this->never())->method('findById');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::isInstanceOf(FindConversationByIdParameters::class))
            ->willReturn($conversation);

        $targetFactory = new TargetFactory($filesystemDocumentRepository, $filesystemImageRepository, $queryService);
        $target        = $targetFactory->create($conversation->id, $conversation::class);

        self::assertSame($conversation->id, $target->getId());
        self::assertSame('Foo Bar Baz Quoz Qu…', $target->getTitle());
    }

    #[Test]
    public function createUnknownTarget(): void
    {
        $this->expectException(UnknownMedium::class);

        $filesystemDocumentRepository = $this->createMock(FilesystemDocumentRepository::class);
        $filesystemDocumentRepository->expects($this->never())->method('findById');

        $filesystemImageRepository = $this->createMock(FilesystemImageRepository::class);
        $filesystemImageRepository->expects($this->never())->method('findById');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $targetFactory = new TargetFactory($filesystemDocumentRepository, $filesystemImageRepository, $queryService);
        $targetFactory->create('123', 'UnknownType');
    }
}
