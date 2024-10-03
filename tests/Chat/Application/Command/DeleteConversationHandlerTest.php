<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Command;

use ChronicleKeeper\Chat\Application\Command\DeleteConversation;
use ChronicleKeeper\Chat\Application\Command\DeleteConversationHandler;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException as WebmozartInvalidArgumentException;

#[CoversClass(DeleteConversationHandler::class)]
#[CoversClass(DeleteConversation::class)]
#[Small]
class DeleteConversationHandlerTest extends TestCase
{
    #[Test]
    public function executeDeletion(): void
    {
        $conversationId = '123e4567-e89b-12d3-a456-426614174000';
        $message        = new DeleteConversation($conversationId);

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('delete')
            ->with('library.conversations', $conversationId . '.json');

        $handler = new DeleteConversationHandler($fileAccess);
        $handler($message);
    }

    #[Test]
    public function validUuid(): void
    {
        $conversationId = '123e4567-e89b-12d3-a456-426614174000';
        $command        = new DeleteConversation($conversationId);
        self::assertSame($conversationId, $command->conversationId);
    }

    #[Test]
    public function invalidUuid(): void
    {
        $this->expectException(WebmozartInvalidArgumentException::class);
        new DeleteConversation('invalid-uuid');
    }
}
