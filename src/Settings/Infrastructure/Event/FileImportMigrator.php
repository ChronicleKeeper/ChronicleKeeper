<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Infrastructure\Event;

use DZunke\NovDoc\Settings\Application\Service\Migrator;
use DZunke\NovDoc\Settings\Domain\Event\FileImported;
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
