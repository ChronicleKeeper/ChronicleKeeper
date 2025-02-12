<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteImageVectorsHandler
{
    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function __invoke(DeleteImageVectors $command): void
    {
        $this->platform->createQueryBuilder()->createDelete()
            ->from('images_vectors')
            ->where('image_id', '=', $command->imageId)
            ->execute();
    }
}
