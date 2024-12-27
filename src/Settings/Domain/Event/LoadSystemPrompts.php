<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\Event;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;

class LoadSystemPrompts
{
    /** @var array<string, SystemPrompt> */
    private array $prompts = [];

    public function add(SystemPrompt $prompt): void
    {
        $this->prompts[$prompt->getId()] = $prompt;
    }

    /** @return array<string, SystemPrompt> */
    public function getPrompts(): array
    {
        return $this->prompts;
    }
}
