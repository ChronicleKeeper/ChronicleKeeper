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
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use function array_key_exists;
use function assert;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final readonly class ConversationImporter implements SingleImport
{
    public function __construct(
        private DatabasePlatform $databasePlatform,
        private DenormalizerInterface $denormalizer,
        private MessageBusInterface $bus,
        private LoggerInterface $logger,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): void
    {
        $libraryDirectoryPath = 'library/conversations/';
        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $content = $filesystem->read($zippedFile->path());
            $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if (array_key_exists('data', $content)) {
                // Workaround for Imports from versions < 0.7
                $content = $content['data'];
            }

            if (
                $settings->overwriteLibrary === false
                && $this->databasePlatform->hasRows('conversations', ['id' => $content['id']])
            ) {
                $this->logger->debug(
                    'Conversation already exists in the database, skipping import',
                    ['id' => $content['id']],
                );

                continue;
            }

            $conversation = $this->denormalizer->denormalize($content, Conversation::class);
            $this->bus->dispatch(new StoreConversation($conversation));
        }
    }
}
