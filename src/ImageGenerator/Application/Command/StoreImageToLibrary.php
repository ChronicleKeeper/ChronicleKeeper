<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Command;

use Webmozart\Assert\Assert;

class StoreImageToLibrary
{
    public function __construct(
        public readonly string $requestId,
        public readonly string $imageId,
    ) {
        Assert::uuid($this->requestId);
        Assert::uuid($this->imageId);
    }
}
