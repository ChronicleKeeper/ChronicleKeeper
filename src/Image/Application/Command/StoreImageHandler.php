<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StoreImageHandler
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
    ) {
    }

    public function __invoke(StoreImage $command): MessageEventResult
    {
        $this->databasePlatform->insertOrUpdate('images', [
            'id' => $command->image->getId(),
            'title' => $command->image->getTitle(),
            'mime_type' => $command->image->getMimeType(),
            'encoded_image' => $command->image->getEncodedImage(),
            'description' => $command->image->getDescription(),
            'directory' => $command->image->getDirectory()->getId(),
            'last_updated' => $command->image->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        return new MessageEventResult($command->image->flushEvents());
    }
}
