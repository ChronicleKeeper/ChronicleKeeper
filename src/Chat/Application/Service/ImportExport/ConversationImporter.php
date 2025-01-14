<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service\ImportExport;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Settings\Application\Service\Importer\SingleImport;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function array_key_exists;
use function assert;
use function json_decode;
use function str_replace;

use const JSON_THROW_ON_ERROR;

final readonly class ConversationImporter implements SingleImport
{
    public function __construct(
        private DatabasePlatform $databasePlatform,
        private DenormalizerInterface $denormalizer,
        private MessageBusInterface $bus,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        $libraryDirectoryPath = 'library/conversations/';

        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $targetFilename = str_replace($libraryDirectoryPath, '', $zippedFile->path());
            assert($targetFilename !== '');

            $content = $filesystem->read($zippedFile->path());
            $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if (
                $settings->overwriteLibrary === false
                && $this->databasePlatform->hasRows('conversations', ['id' => $content['id']])
            ) {
                continue;
            }

            if (array_key_exists('data', $content)) {
                // Workaround for Imports from versions < 0.7
                $content = $content['data'];
            }

            $conversation = $this->denormalizer->denormalize($content, Conversation::class);
            $this->bus->dispatch(new StoreConversation($conversation));
        }
    }
}
