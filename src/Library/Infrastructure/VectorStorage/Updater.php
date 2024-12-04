<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\VectorStorage;

use ChronicleKeeper\Document\Infrastructure\VectorStorage\LibraryDocumentUpdater;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\Updater\LibraryImageUpdater;

class Updater
{
    public function __construct(
        private readonly LibraryDocumentUpdater $libraryDocumentUpdater,
        private readonly LibraryImageUpdater $libraryImageUpdater,
    ) {
    }

    public function updateAll(): void
    {
        $this->libraryDocumentUpdater->updateAll();
        $this->libraryImageUpdater->updateAll();
    }
}
