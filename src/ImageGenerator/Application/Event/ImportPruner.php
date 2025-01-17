<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Event;

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
        $this->logger->debug(
            'Import - Pruning image generations.',
            ['pruner' => self::class, 'tables' => ['generator_requests', 'generator_results']],
        );

        $this->databasePlatform->truncateTable('generator_results');
        $this->databasePlatform->truncateTable('generator_requests');
    }
}
