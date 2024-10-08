<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Domain\ValueObject;

class UserInput
{
    public function __construct(
        public readonly string $prompt,
    ) {
    }
}
