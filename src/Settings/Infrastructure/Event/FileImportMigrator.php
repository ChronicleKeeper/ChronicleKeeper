<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Infrastructure\Event;

use ChronicleKeeper\Settings\Application\Service\Migrator;
use ChronicleKeeper\Settings\Domain\Event\FileImported;
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
