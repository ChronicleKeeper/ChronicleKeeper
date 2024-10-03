<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Command;

use ChronicleKeeper\Chat\Application\Command\ResetTemporaryConversation;
use ChronicleKeeper\Chat\Application\Command\ResetTemporaryConversationHandler;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResetTemporaryConversationHandler::class)]
#[CoversClass(ResetTemporaryConversation::class)]
#[UsesClass(GetTemporaryConversationParameters::class)]
#[Small]
class ResetTemporaryConversationHandlerTest extends TestCase
{
    #[Test]
    public function executeReset(): void
    {
        $message = new ResetTemporaryConversation();

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('delete')
            ->with('temp', 'conversation_temporary.json');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::isInstanceOf(GetTemporaryConversationParameters::class));

        $handler = new ResetTemporaryConversationHandler($fileAccess, $queryService);
        $handler($message);
    }
}
