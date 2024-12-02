<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Library\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorDocumentRepository;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class DeleteDocumentHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly FilesystemVectorDocumentRepository $vectorRepository,
    ) {
    }

    public function __invoke(DeleteDocument $command): void
    {
        foreach ($this->vectorRepository->findAllByDocumentId($command->id) as $vectors) {
            $this->vectorRepository->remove($vectors);
        }

        $this->fileAccess->delete('library.documents', $command->id . '.json');

        $this->eventDispatcher->dispatch(new DocumentDeleted($command->id));
    }
}
