<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\ValueObject;

class MessageDebug
{
    /** @param list<FunctionDebug> $functions */
    public function __construct(
        public readonly array $functions = [],
    ) {
    }
}
