<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Repository;

use ChronicleKeeper\Chat\Application\Entity\Conversation;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

class ConversationFileStorage
{
    public function __construct(
        private readonly string $conversationTemporaryFile,
        private readonly Filesystem $filesystem,
        private readonly SerializerInterface $serializer,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function loadTemporary(): Conversation
    {
        if ($this->filesystem->exists($this->conversationTemporaryFile)) {
            return $this->serializer->deserialize(
                $this->filesystem->readFile($this->conversationTemporaryFile),
                Conversation::class,
                JsonEncoder::FORMAT,
            );
        }

        $conversation = Conversation::createFromSettings($this->settingsHandler->get());
        $this->saveTemporary($conversation);

        return $conversation;
    }

    public function saveTemporary(Conversation $conversation): void
    {
        $this->filesystem->dumpFile(
            $this->conversationTemporaryFile,
            $this->serializer->serialize(
                $conversation,
                JsonEncoder::FORMAT,
                [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            ),
        );
    }

    public function resetTemporary(): void
    {
        $this->filesystem->remove($this->conversationTemporaryFile);
        $this->loadTemporary();
    }
}
