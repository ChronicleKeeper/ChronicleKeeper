<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Event;

use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ExecuteImportPruning::class)]
final class ImportPruner
{
    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(): void
    {
        $this->logger->debug(
            'Import - Pruning favorites.',
            ['pruner' => self::class, 'tables' => ['favorites']],
        );
        try {
            $this->connection->executeStatement('DELETE FROM favorites');
        } catch (Exception $e) {
            $this->logger->error('Failed to prune favorites table', ['error' => $e->getMessage()]);

            throw $e;
        }
    }
}
