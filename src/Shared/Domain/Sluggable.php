<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Domain;

interface Sluggable
{
    public function getSlug(): string;
}
