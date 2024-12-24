<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Infrastructure\Subscriber;

use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBag;
use ChronicleKeeper\Favorizer\Application\Query\GetTargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Library\Domain\Event\ImageDeleted;
use ChronicleKeeper\Shared\Application\Query\QueryService;
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
        $target    = new LibraryImageTarget($event->image->id, 'Irrelevant');

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
        $target    = new LibraryDocumentTarget($event->document->id, 'Irrelevant');

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
        $target    = new ChatConversationTarget($event->conversation->id, 'Irrelevant');

        if (! $targetBag->exists($target)) {
            return;
        }

        $targetBag->remove($target);
        $this->bus->dispatch(new StoreTargetBag($targetBag));
    }
}
