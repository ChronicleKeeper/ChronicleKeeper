<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteDocumentVectorsHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
    ) {
    }

    public function __invoke(DeleteDocumentVectors $command): void
    {
        $this->fileAccess->delete('vector.documents', $command->id . '.json');
    }
}
