<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class GetTemporaryConversationParameters implements QueryParameters
{
    public function getQueryClass(): string
    {
        return GetTemporaryConversationQuery::class;
    }
}
