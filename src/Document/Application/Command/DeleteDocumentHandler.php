<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Library\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorDocumentRepository;
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
        private readonly FilesystemVectorDocumentRepository $vectorRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(DeleteDocument $command): void
    {
        foreach ($this->vectorRepository->findAllByDocumentId($command->id) as $vectors) {
            $this->bus->dispatch(new DeleteDocumentVectors($vectors->id));
        }

        $this->fileAccess->delete('library.documents', $command->id . '.json');

        $this->eventDispatcher->dispatch(new DocumentDeleted($command->id));
    }
}
