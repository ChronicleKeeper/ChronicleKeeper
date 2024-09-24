<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Infrastructure\Event;

use ChronicleKeeper\Settings\Application\Service\Importer\State;
use ChronicleKeeper\Settings\Domain\Event\FileImported;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class FileImportedLogger
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    #[AsEventListener]
    public function log(FileImported $event): void
    {
        if ($event->importedFile->state === State::IGNORED) {
            $this->logger->debug(
                'Ignored import of "' . $event->importedFile->file . '", maybe because it already exists.',
            );
        }

        if ($event->importedFile->state === State::SUCCESS) {
            $this->logger->info('Imported "' . $event->importedFile->file . '"');
        }

        if ($event->importedFile->state !== State::ERROR) {
            return;
        }

        $this->logger->error('Error During File Import for "' . $event->importedFile->file . '"');
    }
}
