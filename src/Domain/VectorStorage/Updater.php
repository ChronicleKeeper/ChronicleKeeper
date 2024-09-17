<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\VectorStorage;

use DZunke\NovDoc\Domain\VectorStorage\Updater\LibraryDocumentUpdater;
use DZunke\NovDoc\Domain\VectorStorage\Updater\LibraryImageUpdater;

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
