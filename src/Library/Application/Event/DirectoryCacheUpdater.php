<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationCreated;
use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Chat\Domain\Event\ConversationMovedToDirectory;
use ChronicleKeeper\Chat\Domain\Event\ConversationRenamed;
use ChronicleKeeper\Document\Domain\Event\DocumentCreated;
use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Document\Domain\Event\DocumentMovedToDirectory;
use ChronicleKeeper\Document\Domain\Event\DocumentRenamed;
use ChronicleKeeper\Image\Domain\Event\ImageCreated;
use ChronicleKeeper\Image\Domain\Event\ImageDeleted;
use ChronicleKeeper\Image\Domain\Event\ImageMovedToDirectory;
use ChronicleKeeper\Image\Domain\Event\ImageRenamed;
use ChronicleKeeper\Library\Application\Service\CacheReader;
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
        $this->cacheReader->refresh($event->image->getDirectory());
    }

    #[AsEventListener]
    public function updateOnImageMovedToDirectory(ImageMovedToDirectory $event): void
    {
        $this->cacheReader->refresh($event->image->getDirectory());
        $this->cacheReader->refresh($event->oldDirectory);
    }

    #[AsEventListener]
    public function updateOnImageRenamed(ImageRenamed $event): void
    {
        $this->cacheReader->refresh($event->image->getDirectory());
    }

    #[AsEventListener]
    public function updateOnImageCreated(ImageCreated $event): void
    {
        $this->cacheReader->refresh($event->image->getDirectory());
    }

    #[AsEventListener]
    public function updateOnDocumentCreated(DocumentCreated $event): void
    {
        $this->cacheReader->refresh($event->document->getDirectory());
    }

    #[AsEventListener]
    public function updateOnDocumentMovedToDirectory(DocumentMovedToDirectory $event): void
    {
        $this->cacheReader->refresh($event->document->getDirectory());
        $this->cacheReader->refresh($event->oldDirectory);
    }

    #[AsEventListener]
    public function updateOnDocumentRenamed(DocumentRenamed $event): void
    {
        $this->cacheReader->refresh($event->document->getDirectory());
    }

    #[AsEventListener]
    public function updateOnDocumentDeleted(DocumentDeleted $event): void
    {
        $this->cacheReader->refresh($event->document->getDirectory());
    }

    #[AsEventListener]
    public function updateOnConversationDeleted(ConversationDeleted $event): void
    {
        $this->cacheReader->refresh($event->conversation->getDirectory());
    }

    #[AsEventListener]
    public function updateOnConversationMovedToDirectory(ConversationMovedToDirectory $event): void
    {
        $this->cacheReader->refresh($event->conversation->getDirectory());
        $this->cacheReader->refresh($event->oldDirectory);
    }

    #[AsEventListener]
    public function updateOnConversationRenamed(ConversationRenamed $event): void
    {
        $this->cacheReader->refresh($event->conversation->getDirectory());
    }

    #[AsEventListener]
    public function updateOnConversationCreated(ConversationCreated $event): void
    {
        $this->cacheReader->refresh($event->conversation->getDirectory());
    }
}
