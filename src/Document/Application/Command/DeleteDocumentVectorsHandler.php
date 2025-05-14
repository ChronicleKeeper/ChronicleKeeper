<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteDocumentVectorsHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(DeleteDocumentVectors $command): void
    {
        $this->connection->createQueryBuilder()
            ->delete('documents_vectors')
            ->where('document_id = :documentId')
            ->setParameter('documentId', $command->documentId)
            ->executeStatement();
    }
}
