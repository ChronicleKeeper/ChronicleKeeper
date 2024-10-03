<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

#[AsMessageHandler]
class StoreTemporaryConversationHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(StoreTemporaryConversation $message): void
    {
        $this->fileAccess->write(
            'temp',
            'conversation_temporary.json',
            $this->serializer->serialize(
                $message->conversation,
                JsonEncoder::FORMAT,
                [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            ),
        );
    }
}
