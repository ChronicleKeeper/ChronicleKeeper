<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Event;

use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Exception\DatabaseQueryException;
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

        try {
            $this->databasePlatform->beginTransaction();

            $this->databasePlatform->createQueryBuilder()->createDelete()->from('generator_results')->execute();
            $this->databasePlatform->createQueryBuilder()->createDelete()->from('generator_requests')->execute();

            $this->databasePlatform->commit();
        } catch (DatabaseQueryException $e) {
            $this->databasePlatform->rollback();
            $this->logger->error(
                'Import - Pruning failed.',
                ['pruner' => self::class, 'error' => $e->getMessage()],
            );

            throw $e;
        }
    }
}
