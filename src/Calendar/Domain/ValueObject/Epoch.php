<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\ValueObject;

readonly class Epoch
{
    public function __construct(
        public string $name,
        public int $beginsInYear,
        public int|null $endsInYear = null,
    ) {
    }

    /** @param array{name: string, startYear: int} $data */
    public static function fromArray(array $data): self
    {
        return new self($data['name'], $data['startYear']);
    }
}
