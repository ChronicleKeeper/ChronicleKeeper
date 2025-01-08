<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Command;

use ChronicleKeeper\Image\Domain\Event\ImageDeleted;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteImageHandler
{
    public function __construct(
        private readonly DatabasePlatform $databasePlatform,
        private readonly FilesystemVectorImageRepository $vectorRepository,
    ) {
    }

    public function __invoke(DeleteImage $command): MessageEventResult
    {
        foreach ($this->vectorRepository->findAllByImageId($command->image->getId()) as $vectors) {
            $this->vectorRepository->remove($vectors);
        }

        $this->databasePlatform->query(
            'DELETE FROM images WHERE id = :id',
            ['id' => $command->image->getId()],
        );

        return new MessageEventResult([new ImageDeleted($command->image)]);
    }
}
