<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Event;

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
            'Import - Pruning documents and their vectors.',
            ['pruner' => self::class, 'tables' => ['images', 'images_vectors']],
        );

        try {
            $this->databasePlatform->beginTransaction();

            $this->databasePlatform->createQueryBuilder()->createDelete()->from('images_vectors')->execute();
            $this->databasePlatform->createQueryBuilder()->createDelete()->from('images')->execute();

            $this->databasePlatform->commit();
        } catch (DatabaseQueryException $e) {
            $this->databasePlatform->rollback();
            $this->logger->error(
                'Import - Pruning failed.',
                ['pruner' => self::class, 'error' => $e->getMessage()],
            );
        }
    }
}
