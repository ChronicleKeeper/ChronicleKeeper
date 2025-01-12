<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use ChronicleKeeper\Image\Domain\Event\ImageDeleted;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DeleteImageHandler
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(DeleteImage $command): MessageEventResult
    {
        $this->bus->dispatch(new DeleteImageVectors($command->image->getId()));

        $this->databasePlatform->query(
            'DELETE FROM images WHERE id = :id',
            ['id' => $command->image->getId()],
        );

        return new MessageEventResult([new ImageDeleted($command->image)]);
    }
}
