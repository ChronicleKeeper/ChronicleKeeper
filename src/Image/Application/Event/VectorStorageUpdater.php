<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Event;

use ChronicleKeeper\Image\Domain\Event\ImageCreated;
use ChronicleKeeper\Image\Domain\Event\ImageDescriptionUpdated;
use ChronicleKeeper\Image\Infrastructure\VectorStorage\LibraryImageUpdater;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class VectorStorageUpdater
{
    public function __construct(
        private readonly LibraryImageUpdater $libraryImageUpdater,
    ) {
    }

    #[AsEventListener]
    public function updateOnImageChangedDescription(ImageDescriptionUpdated $event): void
    {
        $this->libraryImageUpdater->updateOrCreateVectorsForImage($event->image);
    }

    #[AsEventListener]
    public function createOnImageCreation(ImageCreated $event): void
    {
        $this->libraryImageUpdater->updateOrCreateVectorsForImage($event->image);
    }
}
