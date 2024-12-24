<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service\Migrator;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Migrator\FileMigration;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;

use function assert;
use function version_compare;

final readonly class ClearConversations implements FileMigration
{
    public function __construct(
        private FileAccess $fileAccess,
    ) {
    }

    public function isSupporting(FileType $type, string $fileVersion): bool
    {
        return $type === FileType::CHAT_CONVERSATION
            && version_compare($fileVersion, '0.5') >= 0;
    }

    public function migrate(string $file, FileType $type): void
    {
        assert($file !== '');

        $this->fileAccess->delete('library.conversations', $file);
    }
}
