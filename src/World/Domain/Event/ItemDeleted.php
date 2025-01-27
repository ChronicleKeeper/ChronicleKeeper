<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Domain\Event;

use ChronicleKeeper\World\Domain\Entity\Item;

class ItemDeleted
{
    public function __construct(public readonly Item $item)
    {
    }
}
