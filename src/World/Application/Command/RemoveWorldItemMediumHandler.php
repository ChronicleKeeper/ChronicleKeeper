<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveWorldItemMediumHandler
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(RemoveWorldItemMedium $command): void
    {
        $table = match ($command->mediumType) {
            'document' => 'world_item_documents',
            'image' => 'world_item_images',
            'conversation' => 'world_item_conversations',
            default => throw new InvalidArgumentException('Unknown medium type: ' . $command->mediumType),
        };

        $columnName = $command->mediumType . '_id';

        $this->connection->createQueryBuilder()
            ->delete($table)
            ->where('world_item_id = :itemId')
            ->andWhere($columnName . ' = :mediumId')
            ->setParameter('itemId', $command->itemId)
            ->setParameter('mediumId', $command->mediumId)
            ->executeStatement();
    }
}
