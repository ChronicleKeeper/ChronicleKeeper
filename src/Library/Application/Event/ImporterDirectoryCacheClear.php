<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Event;

use ChronicleKeeper\Settings\Domain\Event\ImportFinished;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class ImporterDirectoryCacheClear
{
    public function __construct(
        private readonly FileAccess $fileAccess,
    ) {
    }

    public function __invoke(ImportFinished $event): void
    {
        $this->fileAccess->prune('library.directories.cache');
    }
}
