<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Repository;

use ChronicleKeeper\Chat\Application\Entity\Conversation;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use function count;

use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

class ConversationFileStorage
{
    public function __construct(
        private readonly string $conversationTemporaryFile,
        private readonly string $conversationStoragePath,
        private readonly Filesystem $filesystem,
        private readonly SerializerInterface $serializer,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    /** @return Conversation[] */
    public function findLatestConversations(int $maxEntries): array
    {
        $finder = $this->createFinder()
            ->sortByAccessedTime()
            ->reverseSorting()
            ->files();

        $conversations = [];
        foreach ($finder as $file) {
            $conversations[] = $this->deserialize($file->getRealPath());

            if (count($conversations) === $maxEntries) {
                break;
            }
        }

        return $conversations;
    }

    private function createFinder(): Finder
    {
        return (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->conversationStoragePath);
    }

    private function deserialize(string $file): Conversation
    {
        return $this->serializer->deserialize(
            $this->filesystem->readFile($file),
            Conversation::class,
            JsonEncoder::FORMAT,
        );
    }

    public function load(string $id): Conversation|null
    {
        $filename = $this->conversationStoragePath . DIRECTORY_SEPARATOR . $id . '.json';
        if (! $this->filesystem->exists($filename)) {
            return null;
        }

        return $this->serializer->deserialize(
            $this->filesystem->readFile($filename),
            Conversation::class,
            JsonEncoder::FORMAT,
        );
    }

    public function store(Conversation $conversation): void
    {
        $filename = $this->conversationStoragePath . DIRECTORY_SEPARATOR . $conversation->id . '.json';

        $this->filesystem->dumpFile(
            $filename,
            $this->serializer->serialize(
                $conversation,
                JsonEncoder::FORMAT,
                [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            ),
        );
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
