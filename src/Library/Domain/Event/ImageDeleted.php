<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Domain\Event;

final readonly class ImageDeleted
{
    public function __construct(
        public string $id,
    ) {
    }
}
