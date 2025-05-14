<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use function implode;

#[AsMessageHandler]
class StoreDocumentVectorsHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(StoreDocumentVectors $command): void
    {
        $this->connection->createQueryBuilder()
            ->insert('documents_vectors')
            ->values([
                'document_id' => ':documentId',
                'embedding' => ':embedding',
                'content' => ':content',
                '"vectorContentHash"' => ':vectorContentHash',
            ])
            ->setParameters([
                'documentId' => $command->vectorDocument->document->getId(),
                'embedding' => '[' . implode(',', $command->vectorDocument->vector) . ']',
                'content' => $command->vectorDocument->content,
                'vectorContentHash' => $command->vectorDocument->vectorContentHash,
            ])
            ->executeStatement();
    }
}
