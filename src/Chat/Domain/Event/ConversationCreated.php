<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Event;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;

final readonly class ConversationCreated
{
    public function __construct(
        public Conversation $conversation,
    ) {
    }
}
