<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Application\Query;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.shared.application.query')]
interface Query
{
    public function query(QueryParameters $parameters): mixed;
}
