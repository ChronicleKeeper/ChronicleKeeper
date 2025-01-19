<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use ChronicleKeeper\World\Domain\Event\ItemDeleted;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteWorldItemHandler
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function __invoke(DeleteWorldItem $command): MessageEventResult
    {
        $this->databasePlatform->query(
            'DELETE FROM world_items WHERE id = :id',
            ['id' => $command->item->getId()],
        );

        return new MessageEventResult([new ItemDeleted($command->item)]);
    }
}
