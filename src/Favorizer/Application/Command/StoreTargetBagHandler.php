<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Command;

use Doctrine\DBAL\Connection;
use ReflectionClass;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreTargetBagHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(StoreTargetBag $command): void
    {
        // Clear all existing favorites
        $this->connection->createQueryBuilder()
            ->delete('favorites')
            ->executeStatement();

        // Store the delivered favorites for next fetching
        foreach ($command->targetBag as $target) {
            $this->connection->createQueryBuilder()
                ->insert('favorites')
                ->values([
                    'id' => ':id',
                    'title' => ':title',
                    'type' => ':type',
                ])
                ->setParameter('id', $target->getId())
                ->setParameter('title', $target->getTitle())
                ->setParameter('type', (new ReflectionClass($target))->getShortName())
                ->executeStatement();
        }
    }
}
