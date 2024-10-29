<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Domain;

use ArrayObject;
use ChronicleKeeper\Favorizer\Domain\Entity\Favorite;

use function array_values;

/** @template-extends ArrayObject<int, Favorite> */
class FavoriteBag extends ArrayObject
{
    public function __construct(Favorite ...$favorites)
    {
        parent::__construct(array_values($favorites));
    }
}
