<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Domain\ValueObject;

abstract class DefaultTarget implements Target
{
    public function __construct(
        private readonly string $id,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
