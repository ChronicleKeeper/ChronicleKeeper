<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Event;

use ChronicleKeeper\Document\Domain\Event\DocumentChangedContent;
use ChronicleKeeper\Document\Domain\Event\DocumentCreated;
use ChronicleKeeper\Document\Infrastructure\VectorStorage\LibraryDocumentUpdater;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class VectorStorageUpdater
{
    public function __construct(
        private readonly LibraryDocumentUpdater $libraryDocumentUpdater,
    ) {
    }

    #[AsEventListener]
    public function updateOnDocumentContentChanged(DocumentChangedContent $event): void
    {
        $this->libraryDocumentUpdater->updateOrCreateVectorsForDocument($event->document);
    }

    #[AsEventListener]
    public function createOnDocumentCreated(DocumentCreated $event): void
    {
        $this->libraryDocumentUpdater->updateOrCreateVectorsForDocument($event->document);
    }
}
