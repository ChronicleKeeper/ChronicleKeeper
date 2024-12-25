<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Event;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Library\Domain\Entity\Directory;

final readonly class ConversationMovedToDirectory
{
    public function __construct(
        public Conversation $conversation,
        public Directory $oldDirectory,
    ) {
    }
}
