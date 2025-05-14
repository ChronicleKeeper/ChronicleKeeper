<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteImageVectorsHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(DeleteImageVectors $command): void
    {
        $this->connection->createQueryBuilder()
            ->delete('images_vectors')
            ->where('image_id = :imageId')
            ->setParameter('imageId', $command->imageId)
            ->executeStatement();
    }
}
