<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Webmozart\Assert\Assert;

class GetDocument implements QueryParameters
{
    public function __construct(
        public readonly string $id,
    ) {
        Assert::uuid($id, 'The identifier of the document has to be an UUID.');
    }

    public function getQueryClass(): string
    {
        return GetDocumentQuery::class;
    }
}
