<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Application\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationRenamed;
use ChronicleKeeper\Document\Domain\Event\DocumentRenamed;
use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBag;
use ChronicleKeeper\Favorizer\Application\Event\TargetRenaming;
use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\WorldItemTarget;
use ChronicleKeeper\Image\Domain\Event\ImageRenamed;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Domain\Event\ItemRenamed;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(TargetRenaming::class)]
#[Small]
class TargetRenamingTest extends TestCase
{
    private QueryService&MockObject $queryService;
    private MessageBusInterface&MockObject $bus;
    private TargetRenaming $targetRenaming;

    protected function setUp(): void
    {
        $this->queryService   = $this->createMock(QueryService::class);
        $this->bus            = $this->createMock(MessageBusInterface::class);
        $this->targetRenaming = new TargetRenaming($this->queryService, $this->bus);
    }

    #[Test]
    public function itRenamesConversation(): void
    {
        $conversation = (new ConversationBuilder())->build();
        $event        = new ConversationRenamed($conversation, 'foo');

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(true);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->once())
            ->method('replace')
            ->with(new ChatConversationTarget($conversation->getId(), $conversation->getTitle()));

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new StoreTargetBag($targetBag))
            ->willReturn(new Envelope(new StoreTargetBag($targetBag)));

        $this->targetRenaming->renameOnConversationRenamed($event);
    }

    #[Test]
    public function itDoesNothingWhenConversationNotFound(): void
    {
        $conversation = (new ConversationBuilder())->build();
        $event        = new ConversationRenamed($conversation, 'foo');

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(false);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->never())->method('replace');
        $this->bus->expects($this->never())->method('dispatch');

        $this->targetRenaming->renameOnConversationRenamed($event);
    }

    #[Test]
    public function itRenamesDocument(): void
    {
        $document = (new DocumentBuilder())->build();
        $event    = new DocumentRenamed($document, 'foo');

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(true);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->once())
            ->method('replace')
            ->with(new LibraryDocumentTarget($document->getId(), $document->getTitle()));

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new StoreTargetBag($targetBag))
            ->willReturn(new Envelope(new StoreTargetBag($targetBag)));

        $this->targetRenaming->renameOnDocumentRenamed($event);
    }

    #[Test]
    public function itDoesNothingWhenDocumentNotFound(): void
    {
        $document = (new DocumentBuilder())->build();
        $event    = new DocumentRenamed($document, 'foo');

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(false);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->never())->method('replace');
        $this->bus->expects($this->never())->method('dispatch');

        $this->targetRenaming->renameOnDocumentRenamed($event);
    }

    #[Test]
    public function itRenamesImage(): void
    {
        $image = (new ImageBuilder())->build();
        $event = new ImageRenamed($image, 'foo');

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(true);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->once())
            ->method('replace')
            ->with(new LibraryImageTarget($image->getId(), $image->getTitle()));

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new StoreTargetBag($targetBag))
            ->willReturn(new Envelope(new StoreTargetBag($targetBag)));

        $this->targetRenaming->renameOnImageRenamed($event);
    }

    #[Test]
    public function itDoesNothingWhenImageNotFound(): void
    {
        $image = (new ImageBuilder())->build();
        $event = new ImageRenamed($image, 'foo');

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(false);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->never())->method('replace');
        $this->bus->expects($this->never())->method('dispatch');

        $this->targetRenaming->renameOnImageRenamed($event);
    }

    #[Test]
    public function itRenamesWorldItem(): void
    {
        $item  = (new ItemBuilder())->build();
        $event = new ItemRenamed($item, 'foo');

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(true);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->once())
            ->method('replace')
            ->with(new WorldItemTarget($item->getId(), $item->getName()));

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new StoreTargetBag($targetBag))
            ->willReturn(new Envelope(new StoreTargetBag($targetBag)));

        $this->targetRenaming->renameOnItemRename($event);
    }

    #[Test]
    public function itDoesNothingWhenWorldItemNotFound(): void
    {
        $item  = (new ItemBuilder())->build();
        $event = new ItemRenamed($item, 'foo');

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(false);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->never())->method('replace');
        $this->bus->expects($this->never())->method('dispatch');

        $this->targetRenaming->renameOnItemRename($event);
    }
}
