<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Library\Application\Service\CacheReader;
use ChronicleKeeper\Library\Domain\Event\ImageDeleted;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class DirectoryCacheUpdater
{
    public function __construct(
        private readonly CacheReader $cacheReader,
    ) {
    }

    #[AsEventListener]
    public function updateOnImageDeleted(ImageDeleted $event): void
    {
        $this->cacheReader->refresh($event->image->directory);
    }

    #[AsEventListener]
    public function updateOnDocumentDeleted(DocumentDeleted $event): void
    {
        $this->cacheReader->refresh($event->document->directory);
    }

    #[AsEventListener]
    public function updateOnConversationDeleted(ConversationDeleted $event): void
    {
        $this->cacheReader->refresh($event->conversation->directory);
    }
}
