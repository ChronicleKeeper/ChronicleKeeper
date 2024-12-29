<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Command;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use InvalidArgumentException;

class DeleteSystemPrompt
{
    public function __construct(
        public readonly SystemPrompt $systemPrompt,
    ) {
        if ($this->systemPrompt->isSystem()) {
            throw new InvalidArgumentException('System prompts cannot be stored.');
        }
    }
}
