<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Command;

class DeleteDocument
{
    public function __construct(
        public readonly string $id,
    ) {
    }
}
