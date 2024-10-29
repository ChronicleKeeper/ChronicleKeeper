<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use Webmozart\Assert\Assert;

class StoreGeneratorResult
{
    public function __construct(
        public readonly string $requestId,
        public readonly GeneratorResult $generatorResult,
    ) {
        Assert::uuid($this->requestId);
    }
}
