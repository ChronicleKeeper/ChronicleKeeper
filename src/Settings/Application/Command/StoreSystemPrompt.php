<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Command;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;

class StoreSystemPrompt
{
    public function __construct(
        public readonly SystemPrompt $systemPrompt,
    ) {
    }
}
