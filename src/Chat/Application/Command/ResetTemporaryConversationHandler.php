<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ResetTemporaryConversationHandler
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly QueryService $queryService,
    ) {
    }

    public function __invoke(ResetTemporaryConversation $message): void
    {
        $this->fileAccess->delete('temp', 'conversation_temporary.json');
        $this->queryService->query(new GetTemporaryConversationParameters());
    }
}
