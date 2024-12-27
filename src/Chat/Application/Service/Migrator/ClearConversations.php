<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service\Migrator;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Migrator\FileMigration;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;

use function array_values;
use function assert;
use function json_decode;
use function json_encode;
use function version_compare;

use const JSON_THROW_ON_ERROR;

final readonly class ClearConversations implements FileMigration
{
    public function __construct(
        private FileAccess $fileAccess,
    ) {
    }

    public function isSupporting(FileType $type, string $fileVersion): bool
    {
        return $type === FileType::CHAT_CONVERSATION
            && version_compare($fileVersion, '0.5', '<=') >= 0;
    }

    public function migrate(string $file, FileType $type): void
    {
        assert($file !== '');

        $this->fileAccess->delete('library.conversations', $file);

        // Check if the favorites exist before continue
        if (! $this->fileAccess->exists('storage', 'favorites.json')) {
            return;
        }

        // Load favorites and remove the conversations from the file
        $favorites = $this->fileAccess->read('storage', 'favorites.json');
        $favorites = json_decode($favorites, true, 512, JSON_THROW_ON_ERROR);

        foreach ($favorites as $key => $favorite) {
            if ($favorite['type'] !== 'ChatConversationTarget') {
                continue;
            }

            unset($favorites[$key]);
        }

        $this->fileAccess->write(
            'storage',
            'favorites.json',
            json_encode(array_values($favorites), JSON_THROW_ON_ERROR),
        );
    }
}
