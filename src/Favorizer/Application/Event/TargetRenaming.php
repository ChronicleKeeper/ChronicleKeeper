<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationRenamed;
use ChronicleKeeper\Document\Domain\Event\DocumentRenamed;
use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBag;
use ChronicleKeeper\Favorizer\Application\Query\GetTargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\WorldItemTarget;
use ChronicleKeeper\Image\Domain\Event\ImageRenamed;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Domain\Event\ItemRenamed;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

class TargetRenaming
{
    public function __construct(
        private readonly QueryService $queryService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[AsEventListener(ConversationRenamed::class)]
    public function renameOnConversationRenamed(ConversationRenamed $event): void
    {
        $targetBag = $this->queryService->query(new GetTargetBag());
        $target    = new ChatConversationTarget($event->conversation->getId(), $event->conversation->getTitle());

        if (! $targetBag->exists($target)) {
            return;
        }

        $targetBag->replace($target);
        $this->bus->dispatch(new StoreTargetBag($targetBag));
    }

    #[AsEventListener(DocumentRenamed::class)]
    public function renameOnDocumentRenamed(DocumentRenamed $event): void
    {
        $targetBag = $this->queryService->query(new GetTargetBag());
        $target    = new LibraryDocumentTarget($event->document->getId(), $event->document->getTitle());

        if (! $targetBag->exists($target)) {
            return;
        }

        $targetBag->replace($target);
        $this->bus->dispatch(new StoreTargetBag($targetBag));
    }

    #[AsEventListener(ImageRenamed::class)]
    public function renameOnImageRenamed(ImageRenamed $event): void
    {
        $targetBag = $this->queryService->query(new GetTargetBag());
        $target    = new LibraryImageTarget($event->image->getId(), $event->image->getTitle());

        if (! $targetBag->exists($target)) {
            return;
        }

        $targetBag->replace($target);
        $this->bus->dispatch(new StoreTargetBag($targetBag));
    }

    #[AsEventListener(ItemRenamed::class)]
    public function renameOnItemRename(ItemRenamed $event): void
    {
        $targetBag = $this->queryService->query(new GetTargetBag());
        $target    = new WorldItemTarget($event->item->getId(), $event->item->getName());

        if (! $targetBag->exists($target)) {
            return;
        }

        $targetBag->replace($target);
        $this->bus->dispatch(new StoreTargetBag($targetBag));
    }
}
