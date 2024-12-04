<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Domain\Event;

final readonly class DocumentDeleted
{
    public function __construct(
        public string $id,
    ) {
    }
}
