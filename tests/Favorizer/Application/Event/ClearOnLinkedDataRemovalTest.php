<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Application\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBag;
use ChronicleKeeper\Favorizer\Application\Event\ClearOnLinkedDataRemoval;
use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\WorldItemTarget;
use ChronicleKeeper\Image\Domain\Event\ImageDeleted;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Domain\Event\ItemDeleted;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(ClearOnLinkedDataRemoval::class)]
#[Small]
class ClearOnLinkedDataRemovalTest extends TestCase
{
    private QueryService&MockObject $queryService;
    private MessageBusInterface&MockObject $bus;
    private ClearOnLinkedDataRemoval $clearOnLinkedDataRemoval;

    protected function setUp(): void
    {
        $this->queryService             = $this->createMock(QueryService::class);
        $this->bus                      = $this->createMock(MessageBusInterface::class);
        $this->clearOnLinkedDataRemoval = new ClearOnLinkedDataRemoval($this->queryService, $this->bus);
    }

    #[Test]
    public function itRemovesOnImageDeleted(): void
    {
        $image = (new ImageBuilder())->build();
        $event = new ImageDeleted($image);

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(true);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->once())
            ->method('remove')
            ->with(new LibraryImageTarget($image->getId(), 'Irrelevant'));

        $this->bus
            ->expects($this->once())
            ->method('dispatch')
            ->with(new StoreTargetBag($targetBag))
            ->willReturn(new Envelope(new StoreTargetBag($targetBag)));

        $this->clearOnLinkedDataRemoval->removeOnImageDeleted($event);
    }

    #[Test]
    public function itDoesDoNothingOnImageRemovalIsNotAFavorite(): void
    {
        $image = (new ImageBuilder())->build();
        $event = new ImageDeleted($image);

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(false);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->never())->method('remove');
        $this->bus->expects($this->never())->method('dispatch');

        $this->clearOnLinkedDataRemoval->removeOnImageDeleted($event);
    }

    #[Test]
    public function itRemovesOnDocumentDeleted(): void
    {
        $document = (new DocumentBuilder())->build();
        $event    = new DocumentDeleted($document);

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(true);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->once())
            ->method('remove')
            ->with(new LibraryDocumentTarget($document->getId(), 'Irrelevant'));

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new StoreTargetBag($targetBag))
            ->willReturn(new Envelope(new StoreTargetBag($targetBag)));

        $this->clearOnLinkedDataRemoval->removeOnDocumentDeleted($event);
    }

    #[Test]
    public function itDoesDoNothingOnDocumentRemovalIsNotAFavorite(): void
    {
        $document = (new DocumentBuilder())->build();
        $event    = new DocumentDeleted($document);

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(false);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->never())->method('remove');
        $this->bus->expects($this->never())->method('dispatch');

        $this->clearOnLinkedDataRemoval->removeOnDocumentDeleted($event);
    }

    #[Test]
    public function itRemovesOnConversationDeleted(): void
    {
        $conversation = (new ConversationBuilder())->build();
        $event        = new ConversationDeleted($conversation);

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(true);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->once())
            ->method('remove')
            ->with(new ChatConversationTarget($conversation->getId(), 'Irrelevant'));
        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new StoreTargetBag($targetBag))
            ->willReturn(new Envelope(new StoreTargetBag($targetBag)));

        $this->clearOnLinkedDataRemoval->removeOnConversationDeleted($event);
    }

    #[Test]
    public function itDoesDoNothingOnConversationRemovalIsNotAFavorite(): void
    {
        $conversation = (new ConversationBuilder())->build();
        $event        = new ConversationDeleted($conversation);

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(false);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->never())->method('remove');
        $this->bus->expects($this->never())->method('dispatch');

        $this->clearOnLinkedDataRemoval->removeOnConversationDeleted($event);
    }

    #[Test]
    public function itRemovesOnWorldItemDeleted(): void
    {
        $item  = (new ItemBuilder())->build();
        $event = new ItemDeleted($item);

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(true);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->once())
            ->method('remove')
            ->with(new WorldItemTarget($item->getId(), 'Irrelevant'));

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with(new StoreTargetBag($targetBag))
            ->willReturn(new Envelope(new StoreTargetBag($targetBag)));

        $this->clearOnLinkedDataRemoval->removeOnWorldItemDeleted($event);
    }

    #[Test]
    public function itDoesDoNothingOnWorldItemRemovalIsNotAFavorite(): void
    {
        $item  = (new ItemBuilder())->build();
        $event = new ItemDeleted($item);

        $targetBag = $this->createMock(TargetBag::class);
        $targetBag->method('exists')->willReturn(false);

        $this->queryService->method('query')->willReturn($targetBag);

        $targetBag->expects($this->never())->method('remove');
        $this->bus->expects($this->never())->method('dispatch');

        $this->clearOnLinkedDataRemoval->removeOnWorldItemDeleted($event);
    }
}
