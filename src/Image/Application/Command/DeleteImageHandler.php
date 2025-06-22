<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use ChronicleKeeper\Image\Domain\Event\ImageDeleted;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DeleteImageHandler
{
    public function __construct(
        private readonly Connection $connection,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(DeleteImage $command): MessageEventResult
    {
        $this->bus->dispatch(new DeleteImageVectors($command->image->getId()));

        $this->connection->createQueryBuilder()
            ->delete('images')
            ->where('id = :id')
            ->setParameter('id', $command->image->getId())
            ->executeStatement();

        return new MessageEventResult([new ImageDeleted($command->image)]);
    }
}
