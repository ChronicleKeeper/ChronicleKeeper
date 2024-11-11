<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Event;

final readonly class ConversationDeleted
{
    public function __construct(
        public string $id,
    ) {
    }
}
