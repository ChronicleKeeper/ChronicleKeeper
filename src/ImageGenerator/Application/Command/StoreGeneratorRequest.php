<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;

class StoreGeneratorRequest
{
    public function __construct(
        public readonly GeneratorRequest $request,
    ) {
    }
}
