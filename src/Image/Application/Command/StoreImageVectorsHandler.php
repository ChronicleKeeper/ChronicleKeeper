<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

use function implode;

#[AsMessageHandler]
class StoreImageVectorsHandler
{
    public function __construct(
        private readonly Connection $connection,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(StoreImageVectors $command): void
    {
        // First remove everything
        $this->bus->dispatch(new DeleteImageVectors($command->vectorImage->image->getId()));

        // Then store it again
        $this->connection->createQueryBuilder()
            ->insert('images_vectors')
            ->values([
                'image_id' => ':imageId',
                'embedding' => ':embedding',
                'content' => ':content',
                '"vectorContentHash"' => ':vectorContentHash',
            ])
            ->setParameter('imageId', $command->vectorImage->image->getId())
            ->setParameter('embedding', '[' . implode(',', $command->vectorImage->vector) . ']')
            ->setParameter('content', $command->vectorImage->content)
            ->setParameter('vectorContentHash', $command->vectorImage->vectorContentHash)
            ->executeStatement();
    }
}
