<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteConversationHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
    ) {
    }

    public function __invoke(DeleteConversation $message): void
    {
        $this->fileAccess->delete('library.conversations', $message->conversationId . '.json');
    }
}
