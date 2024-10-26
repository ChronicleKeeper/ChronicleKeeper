<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Domain\ValueObject;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(autowire: false)]
class OptimizedPrompt
{
    public function __construct(
        public readonly string $prompt,
    ) {
    }
}
