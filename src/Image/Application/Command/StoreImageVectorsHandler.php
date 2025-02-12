<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

use function implode;

#[AsMessageHandler]
class StoreImageVectorsHandler
{
    public function __construct(
        private readonly DatabasePlatform $platform,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(StoreImageVectors $command): void
    {
        // First remove everything
        $this->bus->dispatch(new DeleteImageVectors($command->vectorImage->image->getId()));

        // Then store it again
        $this->platform->createQueryBuilder()->createInsert()
            ->insert('images_vectors')
            ->values([
                'image_id' => $command->vectorImage->image->getId(),
                'embedding' => '[' . implode(',', $command->vectorImage->vector) . ']',
                'content' => $command->vectorImage->content,
                'vectorContentHash' => $command->vectorImage->vectorContentHash,
            ])
            ->execute();
    }
}
