<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Domain\ValueObject;

class OptimizedPrompt
{
    public function __construct(
        public readonly string $prompt,
    ) {
    }
}
