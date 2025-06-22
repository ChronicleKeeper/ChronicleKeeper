<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Command;

use ChronicleKeeper\Chat\Application\Command\DeleteConversation;
use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Document\Application\Command\DeleteDocument;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Image\Application\Command\DeleteImage;
use ChronicleKeeper\Image\Application\Query\FindImagesByDirectory;
use ChronicleKeeper\Library\Application\Query\FindDirectoriesByParent;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Event\DirectoryDeleted;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DeleteDirectoryHandler
{
    public function __construct(
        private readonly Connection $connection,
        private readonly MessageBusInterface $bus,
        private readonly QueryService $queryService,
    ) {
    }

    public function __invoke(DeleteDirectory $command): MessageEventResult
    {
        $this->thePurge($command->directory);

        $this->connection->createQueryBuilder()
            ->delete('directories')
            ->where('id = :id')
            ->setParameter('id', $command->directory->getId())
            ->executeStatement();

        return new MessageEventResult([new DirectoryDeleted($command->directory)]);
    }

    private function thePurge(Directory $sourceDirectory): void
    {
        // Delete subdirectories recursively
        foreach ($this->queryService->query(new FindDirectoriesByParent($sourceDirectory->getId())) as $directory) {
            $this->thePurge($directory);
            $this->bus->dispatch(new DeleteDirectory($directory));
        }

        // Delete associated documents
        foreach ($this->queryService->query(new FindDocumentsByDirectory($sourceDirectory->getId())) as $document) {
            $this->bus->dispatch(new DeleteDocument($document));
        }

        // Delete associated images
        foreach ($this->queryService->query(new FindImagesByDirectory($sourceDirectory->getId())) as $image) {
            $this->bus->dispatch(new DeleteImage($image));
        }

        // Delete associated conversations
        foreach ($this->queryService->query(new FindConversationsByDirectoryParameters($sourceDirectory)) as $conversation) {
            $this->bus->dispatch(new DeleteConversation($conversation));
        }
    }
}
