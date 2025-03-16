<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\ValueObject;

readonly class WeekDay
{
    public function __construct(
        public int $index,
        public string $name,
    ) {
    }

    /** @param array{index: int, name: string} $data */
    public static function fromArray(array $data): self
    {
        return new self($data['index'], $data['name']);
    }
}
