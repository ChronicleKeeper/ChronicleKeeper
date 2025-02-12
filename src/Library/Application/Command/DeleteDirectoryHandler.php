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
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DeleteDirectoryHandler
{
    public function __construct(
        private readonly DatabasePlatform $platform,
        private readonly MessageBusInterface $bus,
        private readonly QueryService $queryService,
    ) {
    }

    public function __invoke(DeleteDirectory $command): MessageEventResult
    {
        $this->thePurge($command->directory);

        $this->platform->createQueryBuilder()->createDelete()
            ->from('directories')
            ->where('id', '=', $command->directory->getId())
            ->execute();

        return new MessageEventResult([new DirectoryDeleted($command->directory)]);
    }

    private function thePurge(Directory $sourceDirectory): void
    {
        foreach ($this->queryService->query(new FindDirectoriesByParent($sourceDirectory->getId())) as $directory) {
            $this->thePurge($directory);
            $this->bus->dispatch(new DeleteDirectory($directory));
        }

        foreach ($this->queryService->query(new FindDocumentsByDirectory($sourceDirectory->getId())) as $document) {
            $this->bus->dispatch(new DeleteDocument($document));
        }

        foreach ($this->queryService->query(new FindImagesByDirectory($sourceDirectory->getId())) as $image) {
            $this->bus->dispatch(new DeleteImage($image));
        }

        foreach ($this->queryService->query(new FindConversationsByDirectoryParameters($sourceDirectory)) as $conversation) {
            $this->bus->dispatch(new DeleteConversation($conversation));
        }
    }
}
