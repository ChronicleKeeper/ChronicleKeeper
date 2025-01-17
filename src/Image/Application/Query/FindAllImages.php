<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Query;

use ChronicleKeeper\Shared\Application\Query\QueryParameters;

class FindAllImages implements QueryParameters
{
    public function getQueryClass(): string
    {
        return FindAllImagesQuery::class;
    }
}
