<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use Webmozart\Assert\Assert;

class DeleteGeneratorRequest
{
    public function __construct(
        public readonly string $requestId,
    ) {
        Assert::uuid($this->requestId);
    }
}
