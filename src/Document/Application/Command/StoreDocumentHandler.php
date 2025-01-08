<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreDocumentHandler
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function __invoke(StoreDocument $command): MessageEventResult
    {
        $this->databasePlatform->insertOrUpdate(
            'documents',
            [
                'id' => $command->document->getId(),
                'title' => $command->document->getTitle(),
                'content' => $command->document->getContent(),
                'directory' => $command->document->getDirectory()->getId(),
                'last_updated' => $command->document->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
        );

        return new MessageEventResult($command->document->flushEvents());
    }
}
