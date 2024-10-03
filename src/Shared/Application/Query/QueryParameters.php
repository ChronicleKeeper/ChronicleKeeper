<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Application\Query;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(autowire: false)]
interface QueryParameters
{
    /** @return class-string */
    public function getQueryClass(): string;
}
