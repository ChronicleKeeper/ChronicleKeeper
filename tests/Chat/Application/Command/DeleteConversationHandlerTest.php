<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Command;

use ChronicleKeeper\Chat\Application\Command\DeleteConversation;
use ChronicleKeeper\Chat\Application\Command\DeleteConversationHandler;
use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[CoversClass(DeleteConversationHandler::class)]
#[CoversClass(DeleteConversation::class)]
#[Small]
class DeleteConversationHandlerTest extends TestCase
{
    #[Test]
    public function executeDeletion(): void
    {
        $conversation = (new ConversationBuilder())->build();
        $message      = new DeleteConversation($conversation);

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('delete')
            ->with('library.conversations', $conversation->getId() . '.json');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(ConversationDeleted::class));

        $handler = new DeleteConversationHandler($fileAccess, $eventDispatcher);
        $handler($message);
    }
}
