<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use function implode;

#[AsMessageHandler]
class StoreDocumentVectorsHandler
{
    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function __invoke(StoreDocumentVectors $command): void
    {
        $this->platform->insertOrUpdate(
            'documents_vectors',
            [
                'document_id' => $command->vectorDocument->document->getId(),
                'embedding' => '[' . implode(',', $command->vectorDocument->vector) . ']',
                'content' => $command->vectorDocument->content,
                'vectorContentHash' => $command->vectorDocument->vectorContentHash,
            ],
        );
    }
}
