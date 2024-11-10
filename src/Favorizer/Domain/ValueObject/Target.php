<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Domain\ValueObject;

interface Target
{
    public function getId(): string;

    public function getTitle(): string;
}
