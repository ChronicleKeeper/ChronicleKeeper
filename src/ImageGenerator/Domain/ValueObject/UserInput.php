<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Domain\ValueObject;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(autowire: false)]
class UserInput
{
    public function __construct(
        public readonly string $prompt,
        public SystemPrompt|null $systemPrompt = null,
    ) {
    }
}
