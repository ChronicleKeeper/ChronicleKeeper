<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Command;

use ChronicleKeeper\World\Domain\Entity\Item;

class DeleteWorldItem
{
    public function __construct(
        public readonly Item $item,
    ) {
    }
}
