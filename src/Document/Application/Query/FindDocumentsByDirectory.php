<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Webmozart\Assert\Assert;

class FindDocumentsByDirectory implements QueryParameters
{
    public function __construct(
        public readonly string $id,
    ) {
        Assert::uuid($id);
    }

    public function getQueryClass(): string
    {
        return FindDocumentsByDirectoryQuery::class;
    }
}
