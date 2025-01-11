<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DeleteDocumentHandler
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function __invoke(DeleteDocument $command): MessageEventResult
    {
        $this->bus->dispatch(new DeleteDocumentVectors($command->document->getId()));

        $this->databasePlatform->query(
            'DELETE FROM documents WHERE id = :id',
            ['id' => $command->document->getId()],
        );

        return new MessageEventResult([new DocumentDeleted($command->document)]);
    }
}
