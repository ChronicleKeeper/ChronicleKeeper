<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Webmozart\Assert\Assert;

final class FindAllImagesOfRequest implements QueryParameters
{
    public function __construct(
        public readonly string $requestId,
    ) {
        Assert::uuid($this->requestId);
    }

    public function getQueryClass(): string
    {
        return FindAllImagesOfRequestQuery::class;
    }
}
