<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\ValueObject\Epoch;
use InvalidArgumentException;

use function count;
use function end;
use function sprintf;
use function usort;

class EpochCollection
{
    /** @var list<Epoch> */
    private readonly array $epochs;

    public function __construct(Epoch ...$epochs)
    {
        if ($epochs === []) {
            throw new InvalidArgumentException('At least a single epoch should be given.');
        }

        usort($epochs, static fn (Epoch $a, Epoch $b) => $a->beginsInYear <=> $b->beginsInYear);
        $this->validateEpochs($epochs);

        $this->epochs = $epochs;
    }

    /** @param Epoch[] $epochs */
    private function validateEpochs(array $epochs): void
    {
        $lastEnd = null;
        $count   = count($epochs);

        foreach ($epochs as $index => $epoch) {
            // Check if current epoch starts after previous one ended
            if ($lastEnd !== null && $epoch->beginsInYear <= $lastEnd) {
                throw new InvalidArgumentException(sprintf(
                    'Epochs overlap at year %d to %d',
                    $lastEnd,
                    $epoch->beginsInYear,
                ),);
            }

            // Only the last epoch should have null endYear
            if ($epoch->endsInYear === null && $index !== $count - 1) {
                throw new InvalidArgumentException(
                    'Only the last epoch can have an undefined end year',
                );
            }

            // Check for gaps between epochs
            if ($lastEnd !== null && $epoch->beginsInYear > $lastEnd + 1) {
                throw new InvalidArgumentException(sprintf(
                    'Gap between epochs at year %d to %d',
                    $lastEnd,
                    $epoch->beginsInYear,
                ),);
            }

            $lastEnd = $epoch->endsInYear;
        }

        // Ensure last epoch has no end year
        $lastEntry = end($epochs);
        if ($lastEntry === false) {
            // There was nothing in the array
            return;
        }

        if ($lastEntry->endsInYear !== null) {
            throw new InvalidArgumentException('The last epoch must have an undefined end year');
        }
    }

    /** @param array<array{name: string, startYear: int<0, max>, endYear?: int<0, max>|null}> $epochs */
    public static function fromArray(array $epochs): self
    {
        $collection = [];
        foreach ($epochs as $epoch) {
            $collection[] = new Epoch($epoch['name'], $epoch['startYear'], $epoch['endYear'] ?? null);
        }

        return new self(...$collection);
    }

    /** @return list<Epoch> */
    public function getEpochs(): array
    {
        return $this->epochs;
    }

    public function getEpochForYear(int $year): Epoch
    {
        foreach ($this->epochs as $epoch) {
            if ($epoch->beginsInYear <= $year && ($epoch->endsInYear === null || $year <= $epoch->endsInYear)) {
                return $epoch;
            }
        }

        throw new InvalidArgumentException(sprintf('No epoch found for year %d', $year));
    }
}
