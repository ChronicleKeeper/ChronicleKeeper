<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use function implode;

#[AsMessageHandler]
class StoreImageVectorsHandler
{
    public function __construct(
        private readonly DatabasePlatform $platform,
    ) {
    }

    public function __invoke(StoreImageVectors $command): void
    {
        $this->platform->insertOrUpdate(
            'images_vectors',
            [
                'image_id' => $command->vectorImage->image->getId(),
                'embedding' => '[' . implode(',', $command->vectorImage->vector) . ']',
                'content' => $command->vectorImage->content,
                'vectorContentHash' => $command->vectorImage->vectorContentHash,
            ],
        );
    }
}
