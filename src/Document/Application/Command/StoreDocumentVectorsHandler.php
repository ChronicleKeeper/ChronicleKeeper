<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\SerializerInterface;

#[AsMessageHandler]
class StoreDocumentVectorsHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(StoreDocumentVectors $command): void
    {
        $this->fileAccess->write(
            'vector.documents',
            $command->vectorDocument->id . '.json',
            $this->serializer->serialize($command->vectorDocument, 'json'),
        );
    }
}
