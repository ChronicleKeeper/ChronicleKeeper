<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class DeleteConversationHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(DeleteConversation $message): void
    {
        $this->fileAccess->delete('library.conversations', $message->conversationId . '.json');
        $this->eventDispatcher->dispatch(new ConversationDeleted($message->conversationId));
    }
}
