<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\ValueObject;

class FunctionDebug
{
    /** @param array<string, mixed> $arguments */
    public function __construct(
        public readonly string $tool,
        public readonly array $arguments = [],
        public readonly mixed $result = null,
    ) {
    }
}
