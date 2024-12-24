<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Domain\Entity;

abstract class AggregateRoot
{
    /** @var list<object> */
    private array $events = [];

    protected function record(object $event): void
    {
        $this->events[] = $event;
    }

    /** @return list<object> */
    public function flushEvents(): array
    {
        $events       = $this->events;
        $this->events = [];

        return $events;
    }
}
