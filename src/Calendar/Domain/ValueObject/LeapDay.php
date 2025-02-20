<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\ValueObject;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Day;

final readonly class LeapDay implements Day
{
    public function __construct(
        private int $dayOfTheMonth,
        private string $name,
        public int $yearInterval = 1, // Default is every year
    ) {
    }

    public function getDayOfTheMonth(): int
    {
        return $this->dayOfTheMonth;
    }

    public function getLabel(): string
    {
        return $this->name;
    }

    public function isActiveInYear(int $year): bool
    {
        if ($this->yearInterval === 1) {
            return true;
        }

        return $year % $this->yearInterval === 0;
    }

    /** @param array{day: int, name: string, yearInterval?: int} $data */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['day'],
            $data['name'],
            $data['yearInterval'] ?? 1,
        );
    }
}
