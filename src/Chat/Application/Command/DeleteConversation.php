<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use Webmozart\Assert\Assert;

class DeleteConversation
{
    public function __construct(
        public readonly string $conversationId,
    ) {
        Assert::uuid($this->conversationId, 'To delete a conversation an identifier is needed.');
    }
}
