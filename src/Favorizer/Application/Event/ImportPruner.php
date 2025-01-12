<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Event;

use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ExecuteImportPruning::class)]
class ImportPruner
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExecuteImportPruning $event): void
    {
        if ($event->importSettings->pruneLibrary === false) {
            $this->logger->debug('Import - Skipping pruning of favorites.', ['pruner' => self::class]);

            return;
        }

        $this->logger->debug(
            'Import - Pruning favorites.',
            ['pruner' => self::class, 'tables' => ['favorites']],
        );

        $this->databasePlatform->truncateTable('favorites');
    }
}
