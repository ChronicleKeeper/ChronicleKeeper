<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Domain;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Favorizer\Domain\Exception\UnknownMedium;
use ChronicleKeeper\Favorizer\Domain\TargetFactory;
use ChronicleKeeper\Image\Application\Query\GetImage;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Query\GetWorldItem;
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
            ->withTitle('Foo Bar Baz')
            ->build();

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

        $targetFactory = new TargetFactory($queryService);
        $target        = $targetFactory->create($document->getId(), $document::class);

        self::assertSame($document->getId(), $target->getId());
        self::assertSame('Foo Bar Baz', $target->getTitle());
    }

    #[Test]
    public function createImageTarget(): void
    {
        $image = (new ImageBuilder())
            ->withTitle('Foo Bar Baz')
            ->build();

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) use ($image): object {
                    if ($query instanceof GetImage) {
                        return $image;
                    }

                    self::fail('Unexpected query ' . $query::class);
                },
            );

        $targetFactory = new TargetFactory($queryService);
        $target        = $targetFactory->create($image->getId(), $image::class);

        self::assertSame($image->getId(), $target->getId());
        self::assertSame('Foo Bar Baz', $target->getTitle());
    }

    #[Test]
    public function createConversationTarget(): void
    {
        $conversation = (new ConversationBuilder())
            ->withTitle('Foo Bar Baz')
            ->build();

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

        $targetFactory = new TargetFactory($queryService);
        $target        = $targetFactory->create($conversation->getId(), $conversation::class);

        self::assertSame($conversation->getId(), $target->getId());
        self::assertSame('Foo Bar Baz', $target->getTitle());
    }

    #[Test]
    public function createWorldItemTarget(): void
    {
        $item = (new ItemBuilder())->withName('Foo Bar Baz')->build();

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (QueryParameters $query) use ($item): object {
                    if ($query instanceof GetWorldItem) {
                        return $item;
                    }

                    throw new UnexpectedValueException('Unexpected query.');
                },
            );

        $targetFactory = new TargetFactory($queryService);
        $target        = $targetFactory->create($item->getId(), $item::class);

        self::assertSame($item->getId(), $target->getId());
        self::assertSame('Foo Bar Baz', $target->getTitle());
    }

    #[Test]
    public function createUnknownTarget(): void
    {
        $this->expectException(UnknownMedium::class);

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $targetFactory = new TargetFactory($queryService);
        $targetFactory->create('123', 'UnknownType');
    }
}
