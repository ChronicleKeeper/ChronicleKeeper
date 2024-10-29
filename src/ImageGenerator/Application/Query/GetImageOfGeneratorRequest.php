<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Webmozart\Assert\Assert;

final readonly class GetImageOfGeneratorRequest implements QueryParameters
{
    public function __construct(
        public string $requestId,
        public string $imageId,
    ) {
        Assert::uuid($this->requestId);
        Assert::uuid($this->imageId);
    }

    public function getQueryClass(): string
    {
        return GetImageOfGeneratorRequestQuery::class;
    }
}
