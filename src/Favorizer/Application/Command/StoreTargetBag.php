<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Command;

use ChronicleKeeper\Favorizer\Domain\TargetBag;

class StoreTargetBag
{
    public function __construct(
        public readonly TargetBag $targetBag,
    ) {
    }
}
