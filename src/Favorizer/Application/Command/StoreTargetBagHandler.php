<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ReflectionClass;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreTargetBagHandler
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function __invoke(StoreTargetBag $command): void
    {
        // Clear all existing favorites
        $this->databasePlatform->createQueryBuilder()->createDelete()->from('favorites')->execute();

        // Store the delivered favorites for next fetching
        foreach ($command->targetBag as $target) {
            $this->databasePlatform->createQueryBuilder()->createInsert()
                ->insert('favorites')
                ->values([
                    'id' => $target->getId(),
                    'title' => $target->getTitle(),
                    'type' => (new ReflectionClass($target))->getShortName(),
                ])
                ->execute();
        }
    }
}
