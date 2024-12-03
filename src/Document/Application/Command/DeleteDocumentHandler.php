<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Document\Application\Query\FindVectorsOfDocument;
use ChronicleKeeper\Library\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class DeleteDocumentHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MessageBusInterface $bus,
        private readonly QueryService $queryService,
    ) {
    }

    public function __invoke(DeleteDocument $command): void
    {
        foreach ($this->queryService->query(new FindVectorsOfDocument($command->id)) as $vectors) {
            $this->bus->dispatch(new DeleteDocumentVectors($vectors->id));
        }

        $this->fileAccess->delete('library.documents', $command->id . '.json');

        $this->eventDispatcher->dispatch(new DocumentDeleted($command->id));
    }
}
