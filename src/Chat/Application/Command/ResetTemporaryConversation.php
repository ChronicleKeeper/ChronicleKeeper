<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;

class ResetTemporaryConversation
{
    public function __construct(
        public readonly string $title,
        public readonly SystemPrompt $utilizePrompt,
    ) {
    }
}
