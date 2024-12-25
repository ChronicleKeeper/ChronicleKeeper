<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Event;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;

final readonly class ConversationSettingsChanged
{
    public function __construct(
        public Conversation $conversation,
        public Settings $oldSettings,
    ) {
    }
}
