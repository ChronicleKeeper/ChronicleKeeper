<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\SerializerInterface;

#[AsMessageHandler]
class StoreDocumentHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(StoreDocument $command): void
    {
        if ($command->updateTimestamp) {
            $command->document->updatedAt = $this->clock->now();
        }

        $this->fileAccess->write(
            'library.documents',
            $command->document->id . '.json',
            $this->serializer->serialize($command->document, 'json'),
        );
    }
}
