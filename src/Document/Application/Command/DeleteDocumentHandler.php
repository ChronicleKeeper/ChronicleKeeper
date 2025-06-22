<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DeleteDocumentHandler
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(DeleteDocument $command): MessageEventResult
    {
        $this->bus->dispatch(new DeleteDocumentVectors($command->document->getId()));

        $this->connection->createQueryBuilder()
            ->delete('documents')
            ->where('id = :id')
            ->setParameter('id', $command->document->getId())
            ->executeStatement();

        return new MessageEventResult([new DocumentDeleted($command->document)]);
    }
}
