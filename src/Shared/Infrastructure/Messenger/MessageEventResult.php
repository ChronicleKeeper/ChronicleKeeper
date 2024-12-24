<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Messenger;

class MessageEventResult
{
    /** @param list<object> $events */
    public function __construct(private readonly array $events = [])
    {
    }

    /** @return list<object> */
    public function getEvents(): array
    {
        return $this->events;
    }
}
