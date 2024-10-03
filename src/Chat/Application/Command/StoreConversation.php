<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;

class StoreConversation
{
    public function __construct(
        public readonly Conversation $conversation,
    ) {
    }
}
