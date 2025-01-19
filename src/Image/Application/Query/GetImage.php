<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Webmozart\Assert\Assert;

class GetImage implements QueryParameters
{
    public function __construct(
        public readonly string $id,
    ) {
        Assert::uuid($id, 'The identifier of the image has to be an UUID.');
    }

    public function getQueryClass(): string
    {
        return GetImageQuery::class;
    }
}
