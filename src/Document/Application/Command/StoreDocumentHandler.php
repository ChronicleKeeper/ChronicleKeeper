<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\SerializerInterface;

#[AsMessageHandler]
class StoreDocumentHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(StoreDocument $command): MessageEventResult
    {
        $this->fileAccess->write(
            'library.documents',
            $command->document->getId() . '.json',
            $this->serializer->serialize($command->document, 'json'),
        );

        return new MessageEventResult($command->document->flushEvents());
    }
}
