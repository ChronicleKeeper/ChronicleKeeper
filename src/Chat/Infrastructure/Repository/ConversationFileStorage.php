<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Repository;

use ChronicleKeeper\Chat\Application\Entity\Conversation;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

use function array_filter;
use function count;
use function strcasecmp;
use function usort;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

class ConversationFileStorage
{
    private const string STORAGE_NAME = 'library.conversations';

    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly SettingsHandler $settingsHandler,
        private readonly LoggerInterface $logger,
        private readonly PathRegistry $pathRegistry,
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
            $conversations[] = $this->deserialize($file->getFilename());

            if (count($conversations) === $maxEntries) {
                break;
            }
        }

        return $conversations;
    }

    /** @return Conversation[] */
    public function findByDirectory(Directory $directory): array
    {
        $finder = $this->createFinder()->files();

        $conversations = [];
        foreach ($finder as $file) {
            try {
                $conversations[] = $this->deserialize($file->getFilename());
            } catch (RuntimeException $e) {
                $this->logger->error($e, ['file' => $file]);
            }
        }

        $conversations = array_filter(
            $conversations,
            static fn (Conversation $conversation) => $conversation->directory->id === $directory->id,
        );

        usort(
            $conversations,
            static fn (Conversation $left, Conversation $right) => strcasecmp($left->title, $right->title),
        );

        return $conversations;
    }

    private function createFinder(): Finder
    {
        return (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->pathRegistry->get(self::STORAGE_NAME));
    }

    private function deserialize(string $file): Conversation
    {
        Assert::notEmpty($file, 'The given file must not be empty.');

        return $this->serializer->deserialize(
            $this->fileAccess->read(self::STORAGE_NAME, $file),
            Conversation::class,
            JsonEncoder::FORMAT,
        );
    }

    public function delete(Conversation $conversation): void
    {
        $this->fileAccess->delete(self::STORAGE_NAME, $conversation->id . '.json');
    }

    public function load(string $id): Conversation|null
    {
        $filename = $id . '.json';
        if (! $this->fileAccess->exists(self::STORAGE_NAME, $filename)) {
            return null;
        }

        return $this->serializer->deserialize(
            $this->fileAccess->read(self::STORAGE_NAME, $filename),
            Conversation::class,
            JsonEncoder::FORMAT,
        );
    }

    public function store(Conversation $conversation): void
    {
        $this->fileAccess->write(
            self::STORAGE_NAME,
            $conversation->id . '.json',
            $this->serializer->serialize(
                $conversation,
                JsonEncoder::FORMAT,
                [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            ),
        );
    }

    public function loadTemporary(): Conversation
    {
        try {
            return $this->serializer->deserialize(
                $this->fileAccess->read('temp', 'conversation_temporary.json'),
                Conversation::class,
                JsonEncoder::FORMAT,
            );
        } catch (UnableToReadFile) {
            // All is fine, file not exists ... we create it.

            $conversation = Conversation::createFromSettings($this->settingsHandler->get());
            $this->saveTemporary($conversation);

            return $conversation;
        }
    }

    public function saveTemporary(Conversation $conversation): void
    {
        $this->fileAccess->write(
            'temp',
            'conversation_temporary.json',
            $this->serializer->serialize(
                $conversation,
                JsonEncoder::FORMAT,
                [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            ),
        );
    }

    public function resetTemporary(): void
    {
        $this->fileAccess->delete('temp', 'conversation_temporary.json');
        $this->loadTemporary();
    }
}
