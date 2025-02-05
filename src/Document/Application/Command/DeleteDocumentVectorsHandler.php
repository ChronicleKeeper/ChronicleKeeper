<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteDocumentVectorsHandler
{
    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function __invoke(DeleteDocumentVectors $command): void
    {
        $this->platform->createQueryBuilder()->createDelete()
            ->from('documents_vectors')
            ->where('document_id', '=', $command->documentId)
            ->execute();
    }
}
