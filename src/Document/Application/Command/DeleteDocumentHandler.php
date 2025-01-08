<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Document\Application\Query\FindVectorsOfDocument;
use ChronicleKeeper\Document\Domain\Event\DocumentDeleted;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DeleteDocumentHandler
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly QueryService $queryService,
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function __invoke(DeleteDocument $command): MessageEventResult
    {
        foreach ($this->queryService->query(new FindVectorsOfDocument($command->document->getId())) as $vectors) {
            $this->bus->dispatch(new DeleteDocumentVectors($vectors->id));
        }

        $this->databasePlatform->query(
            'DELETE FROM documents WHERE id = :id',
            ['id' => $command->document->getId()],
        );

        return new MessageEventResult([new DocumentDeleted($command->document)]);
    }
}
