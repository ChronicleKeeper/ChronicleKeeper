<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Webmozart\Assert\Assert;

final class GetGeneratorRequest implements QueryParameters
{
    public function __construct(
        public readonly string $id,
    ) {
        Assert::uuid($this->id);
    }

    public function getQueryClass(): string
    {
        return GetGeneratorRequestQuery::class;
    }
}
