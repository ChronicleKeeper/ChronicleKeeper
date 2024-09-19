<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Listener;

use DZunke\NovDoc\Infrastructure\Application\Migrator;
use DZunke\NovDoc\Infrastructure\Event\FileImported;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class FileImportMigrator
{
    public function __construct(private readonly Migrator $migrator)
    {
    }

    #[AsEventListener]
    public function migrate(FileImported $event): void
    {
        $this->migrator->migrate(
            $event->importedFile->file,
            $event->importedFile->type,
            $event->version,
        );
    }
}
