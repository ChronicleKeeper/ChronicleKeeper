<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Domain\Event;

use ChronicleKeeper\World\Domain\Entity\Item;

class ItemChangedDescription
{
    public function __construct(public readonly Item $item, public string $oldDescription)
    {
    }
}
