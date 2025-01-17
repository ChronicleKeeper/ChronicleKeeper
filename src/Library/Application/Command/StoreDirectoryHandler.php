<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreDirectoryHandler
{
    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function __invoke(StoreDirectory $command): MessageEventResult
    {
        $this->platform->insertOrUpdate('directories', [
            'id' => $command->directory->getId(),
            'title' => $command->directory->getTitle(),
            'parent' => $command->directory->getParent()?->getId(),
        ]);

        return new MessageEventResult($command->directory->flushEvents());
    }
}
