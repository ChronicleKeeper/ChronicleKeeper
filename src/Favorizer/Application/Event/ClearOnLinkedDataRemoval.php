<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBag;
use ChronicleKeeper\Favorizer\Application\Query\GetTargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\WorldItemTarget;
use ChronicleKeeper\Image\Domain\Event\ImageDeleted;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Domain\Event\ItemDeleted;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

class ClearOnLinkedDataRemoval
{
    public function __construct(
        private readonly QueryService $queryService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[AsEventListener(ImageDeleted::class)]
    public function removeOnImageDeleted(ImageDeleted $event): void
    {
        $targetBag = $this->queryService->query(new GetTargetBag());
        $target    = new LibraryImageTarget($event->image->getId(), 'Irrelevant');

        if (! $targetBag->exists($target)) {
            return;
        }

        $targetBag->remove($target);
        $this->bus->dispatch(new StoreTargetBag($targetBag));
    }

    #[AsEventListener(DocumentDeleted::class)]
    public function removeOnDocumentDeleted(DocumentDeleted $event): void
    {
        $targetBag = $this->queryService->query(new GetTargetBag());
        $target    = new LibraryDocumentTarget($event->document->getId(), 'Irrelevant');

        if (! $targetBag->exists($target)) {
            return;
        }

        $targetBag->remove($target);
        $this->bus->dispatch(new StoreTargetBag($targetBag));
    }

    #[AsEventListener(ConversationDeleted::class)]
    public function removeOnConversationDeleted(ConversationDeleted $event): void
    {
        $targetBag = $this->queryService->query(new GetTargetBag());
        $target    = new ChatConversationTarget($event->conversation->getId(), 'Irrelevant');

        if (! $targetBag->exists($target)) {
            return;
        }

        $targetBag->remove($target);
        $this->bus->dispatch(new StoreTargetBag($targetBag));
    }

    #[AsEventListener(ItemDeleted::class)]
    public function removeOnWorldItemDeleted(ItemDeleted $event): void
    {
        $targetBag = $this->queryService->query(new GetTargetBag());
        $target    = new WorldItemTarget($event->item->getId(), 'Irrelevant');

        if (! $targetBag->exists($target)) {
            return;
        }

        $targetBag->remove($target);
        $this->bus->dispatch(new StoreTargetBag($targetBag));
    }
}
