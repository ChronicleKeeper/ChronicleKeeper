<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Webmozart\Assert\Assert;

class FindConversationByIdParameters implements QueryParameters
{
    public function __construct(
        public readonly string $id,
    ) {
        Assert::uuid($id, 'The identifier of the conversation has to be an UUID.');
    }

    public function getQueryClass(): string
    {
        return FindConversationByIdQuery::class;
    }
}
