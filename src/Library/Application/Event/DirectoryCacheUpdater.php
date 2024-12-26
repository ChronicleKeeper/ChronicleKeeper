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
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Event\DirectoryDeleted;
use ChronicleKeeper\Library\Domain\Event\DirectoryMovedToDirectory;
use ChronicleKeeper\Library\Domain\Event\DirectoryRenamed;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

use function assert;

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

    #[AsEventListener]
    public function updateOnDirectoryDeleted(DirectoryDeleted $event): void
    {
        $this->cacheReader->remove($event->directory);

        $parentDirectory = $event->directory->getParent();
        if (! $parentDirectory instanceof Directory) {
            return;
        }

        $this->cacheReader->refresh($parentDirectory);
    }

    #[AsEventListener]
    public function updateOnDirectoryMovedToDirectory(DirectoryMovedToDirectory $event): void
    {
        $parentDirectory = $event->directory->getParent();
        assert($parentDirectory instanceof Directory); // Ensured by tree logic, as the root can not be moved

        $this->cacheReader->refresh($parentDirectory);
        $this->cacheReader->refresh($event->oldParent);
    }

    #[AsEventListener]
    public function updateOnDirectoryRenamed(DirectoryRenamed $event): void
    {
        $this->cacheReader->refresh($event->directory);
    }
}
