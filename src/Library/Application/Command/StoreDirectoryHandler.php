<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Command;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreDirectoryHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(StoreDirectory $command): MessageEventResult
    {
        $directory = $command->directory;
        $this->saveDirectory($directory);

        return new MessageEventResult($directory->flushEvents());
    }

    private function saveDirectory(Directory $directory): void
    {
        // Check if directory already exists
        $existing = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('directories')
            ->where('id = :id')
            ->setParameter('id', $directory->getId())
            ->executeQuery()
            ->fetchAssociative();

        if ($existing !== false) {
            // Update existing directory
            $this->connection->createQueryBuilder()
                ->update('directories')
                ->set('title', ':title')
                ->set('parent', ':parent')
                ->where('id = :id')
                ->setParameter('id', $directory->getId())
                ->setParameter('title', $directory->getTitle())
                ->setParameter('parent', $directory->getParent()?->getId())
                ->executeStatement();
        } else {
            // Insert new directory
            $this->connection->createQueryBuilder()
                ->insert('directories')
                ->values([
                    'id' => ':id',
                    'title' => ':title',
                    'parent' => ':parent',
                ])
                ->setParameter('id', $directory->getId())
                ->setParameter('title', $directory->getTitle())
                ->setParameter('parent', $directory->getParent()?->getId())
                ->executeStatement();
        }
    }
}
