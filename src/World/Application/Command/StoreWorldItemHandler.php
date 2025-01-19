<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreWorldItemHandler
{
    public function __construct(private readonly DatabasePlatform $platform)
    {
    }

    public function __invoke(StoreWorldItem $command): void
    {
        $this->platform->insertOrUpdate(
            'world_items',
            [
                'id' => $command->item->getId(),
                'type' => $command->item->getType()->value,
                'name' => $command->item->getName(),
                'short_description' => $command->item->getShortDescription(),
            ],
        );
    }
}
