<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreImageHandler
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(StoreImage $command): MessageEventResult
    {
        // Check if the image already exists
        $existingImage = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('images')
            ->where('id = :id')
            ->setParameter('id', $command->image->getId())
            ->executeQuery()
            ->fetchOne();

        if ($existingImage !== false) {
            // Update existing image
            $this->connection->createQueryBuilder()
                ->update('images')
                ->set('title', ':title')
                ->set('mime_type', ':mimeType')
                ->set('encoded_image', ':encodedImage')
                ->set('description', ':description')
                ->set('directory', ':directory')
                ->set('last_updated', ':lastUpdated')
                ->where('id = :id')
                ->setParameter('id', $command->image->getId())
                ->setParameter('title', $command->image->getTitle())
                ->setParameter('mimeType', $command->image->getMimeType())
                ->setParameter('encodedImage', $command->image->getEncodedImage())
                ->setParameter('description', $command->image->getDescription())
                ->setParameter('directory', $command->image->getDirectory()->getId())
                ->setParameter('lastUpdated', $command->image->getUpdatedAt()->format('Y-m-d H:i:s'))
                ->executeStatement();
        } else {
            // Insert new image
            $this->connection->createQueryBuilder()
                ->insert('images')
                ->values([
                    'id' => ':id',
                    'title' => ':title',
                    'mime_type' => ':mimeType',
                    'encoded_image' => ':encodedImage',
                    'description' => ':description',
                    'directory' => ':directory',
                    'last_updated' => ':lastUpdated',
                ])
                ->setParameter('id', $command->image->getId())
                ->setParameter('title', $command->image->getTitle())
                ->setParameter('mimeType', $command->image->getMimeType())
                ->setParameter('encodedImage', $command->image->getEncodedImage())
                ->setParameter('description', $command->image->getDescription())
                ->setParameter('directory', $command->image->getDirectory()->getId())
                ->setParameter('lastUpdated', $command->image->getUpdatedAt()->format('Y-m-d H:i:s'))
                ->executeStatement();
        }

        return new MessageEventResult($command->image->flushEvents());
    }
}
