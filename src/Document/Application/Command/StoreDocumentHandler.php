<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreDocumentHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(StoreDocument $command): MessageEventResult
    {
        // Check if document exists
        $documentExists = $this->connection->createQueryBuilder()
            ->select('1')
            ->from('documents')
            ->where('id = :id')
            ->setParameter('id', $command->document->getId())
            ->executeQuery()
            ->fetchOne();

        if ($documentExists !== false) {
            // Update existing document
            $this->connection->createQueryBuilder()
                ->update('documents')
                ->set('title', ':title')
                ->set('content', ':content')
                ->set('directory', ':directory')
                ->set('last_updated', ':last_updated')
                ->where('id = :id')
                ->setParameters([
                    'id' => $command->document->getId(),
                    'title' => $command->document->getTitle(),
                    'content' => $command->document->getContent(),
                    'directory' => $command->document->getDirectory()->getId(),
                    'last_updated' => $command->document->getUpdatedAt()->format('Y-m-d H:i:s'),
                ])
                ->executeStatement();
        } else {
            // Insert new document
            $this->connection->createQueryBuilder()
                ->insert('documents')
                ->values([
                    'id' => ':id',
                    'title' => ':title',
                    'content' => ':content',
                    'directory' => ':directory',
                    'last_updated' => ':last_updated',
                ])
                ->setParameters([
                    'id' => $command->document->getId(),
                    'title' => $command->document->getTitle(),
                    'content' => $command->document->getContent(),
                    'directory' => $command->document->getDirectory()->getId(),
                    'last_updated' => $command->document->getUpdatedAt()->format('Y-m-d H:i:s'),
                ])
                ->executeStatement();
        }

        return new MessageEventResult($command->document->flushEvents());
    }
}
