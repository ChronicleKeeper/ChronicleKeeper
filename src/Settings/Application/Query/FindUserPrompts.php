<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

final class FindUserPrompts implements QueryParameters
{
    public function getQueryClass(): string
    {
        return FindUserPromptsQuery::class;
    }
}
