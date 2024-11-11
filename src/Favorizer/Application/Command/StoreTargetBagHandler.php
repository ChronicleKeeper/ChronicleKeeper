<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

#[AsMessageHandler]
class StoreTargetBagHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(StoreTargetBag $command): void
    {
        $this->fileAccess->write(
            'storage',
            'favorites.json',
            $this->serializer->serialize(
                $command->targetBag,
                JsonEncoder::FORMAT,
                [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            ),
        );
    }
}
