<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\Infrastructure\VectorStorage;

use DZunke\NovDoc\Library\Infrastructure\VectorStorage\Updater\LibraryDocumentUpdater;
use DZunke\NovDoc\Library\Infrastructure\VectorStorage\Updater\LibraryImageUpdater;

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
