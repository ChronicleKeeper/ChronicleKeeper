<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Entity;

use ArrayObject;

use function array_values;

/** @extends ArrayObject<int, Message> */
class MessageBag extends ArrayObject
{
    public function __construct(Message ...$messages)
    {
        parent::__construct(array_values($messages));
    }

    public function reset(): void
    {
        $this->exchangeArray([]);
    }
}
