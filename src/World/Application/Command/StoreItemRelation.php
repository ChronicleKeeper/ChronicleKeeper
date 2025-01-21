<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

class StoreItemRelation
{
    public function __construct(
        public readonly string $sourceItemId,
        public readonly string $targetItemId,
        public readonly string $relationType,
    ) {
    }
}
