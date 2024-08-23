<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Prompts;

final readonly class Prompt
{
    public function __construct(
        public string $name,
        public string $prompt,
    ) {
    }
}
