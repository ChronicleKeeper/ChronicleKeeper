<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Domain\ValueObject;

use ChronicleKeeper\World\Domain\Entity\Item;

readonly class Relation
{
    public function __construct(
        public Item $toItem,
        public string $relationType,
    ) {
    }
}
