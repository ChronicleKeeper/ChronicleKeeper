<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Domain;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Favorizer\Domain\Exception\UnknownMedium;
use ChronicleKeeper\Favorizer\Domain\TargetFactory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

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

        $filesystemImageRepository = $this->createMock(FilesystemImageRepository::class);
        $filesystemImageRepository->expects($this->never())->method('findById');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) use ($document): object {
                    if ($query instanceof GetDocument) {
                        self::assertSame($document->getId(), $query->id);

                        return $document;
                    }

                    throw new UnexpectedValueException('Unexpected query.');
                },
            );

        $targetFactory = new TargetFactory($filesystemImageRepository, $queryService);
        $target        = $targetFactory->create($document->getId(), $document::class);

        self::assertSame($document->getId(), $target->getId());
        self::assertSame('Foo Bar Baz Quoz Qu…', $target->getTitle());
    }

    #[Test]
    public function createImageTarget(): void
    {
        $image = (new ImageBuilder())
            ->withTitle('Foo Bar Baz Quoz Quux Corge Grault Garply Waldo Fred Plugh Xyzzy Thud')
            ->build();

        $filesystemImageRepository = $this->createMock(FilesystemImageRepository::class);
        $filesystemImageRepository->expects($this->once())
            ->method('findById')
            ->with($image->getId())
            ->willReturn($image);

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $targetFactory = new TargetFactory($filesystemImageRepository, $queryService);
        $target        = $targetFactory->create($image->getId(), $image::class);

        self::assertSame($image->getId(), $target->getId());
        self::assertSame('Foo Bar Baz Quoz Qu…', $target->getTitle());
    }

    #[Test]
    public function createConversationTarget(): void
    {
        $conversation              = (new ConversationBuilder())
            ->withTitle('Foo Bar Baz Quoz Quux Corge Grault Garply Waldo Fred Plugh Xyzzy Thud')
            ->build();
        $filesystemImageRepository = $this->createMock(FilesystemImageRepository::class);
        $filesystemImageRepository->expects($this->never())->method('findById');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) use ($conversation): object {
                    if ($query instanceof FindConversationByIdParameters) {
                        return $conversation;
                    }

                    throw new UnexpectedValueException('Unexpected query.');
                },
            );

        $targetFactory = new TargetFactory($filesystemImageRepository, $queryService);
        $target        = $targetFactory->create($conversation->getId(), $conversation::class);

        self::assertSame($conversation->getId(), $target->getId());
        self::assertSame('Foo Bar Baz Quoz Qu…', $target->getTitle());
    }

    #[Test]
    public function createUnknownTarget(): void
    {
        $this->expectException(UnknownMedium::class);

        $filesystemImageRepository = $this->createMock(FilesystemImageRepository::class);
        $filesystemImageRepository->expects($this->never())->method('findById');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $targetFactory = new TargetFactory($filesystemImageRepository, $queryService);
        $targetFactory->create('123', 'UnknownType');
    }
}
