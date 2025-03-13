<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Exception\DayNotExistsInMonth;
use ChronicleKeeper\Calendar\Domain\ValueObject\LeapDay;
use ChronicleKeeper\Calendar\Domain\ValueObject\RegularDay;
use Countable;

use function array_combine;
use function array_filter;
use function array_key_exists;
use function array_map;
use function count;

final class DayCollection implements Countable
{
    /** @var array<int, LeapDay> */
    private readonly array $leapDays;

    /** @var array<int, Day> */
    private array $daysInTheMonth;
    /** @var array<int, array<int, Day>> */
    private array $daysInMonthOfYear = [];

    /** @param int<0, max> $amountOfRegularDays */
    public function __construct(
        private readonly int $amountOfRegularDays,
        LeapDay ...$leapDays,
    ) {
        // Reformat the leap days array to be indexed by the dayOfTheMonth setting
        $leapDays = array_combine(
            array_map(static fn (LeapDay $day): int => $day->getDayOfTheMonth(), $leapDays),
            $leapDays,
        );

        $this->leapDays       = $leapDays;
        $this->daysInTheMonth = $this->calculateDaysInTheMonth($leapDays);
    }

    /**
     * @param array<int, LeapDay> $leapDays
     *
     * @return array<int, Day>
     */
    private function calculateDaysInTheMonth(array $leapDays, int|null $year = null): array
    {
        $daysInTheMonth = [];

        $dayOfTheMonthToCheck       = 1;
        $numericRegularDayToDisplay = 1;
        do {
            if (array_key_exists($dayOfTheMonthToCheck, $leapDays)) {
                $leapDay = $leapDays[$dayOfTheMonthToCheck];

                if ($year !== null && ! $leapDay->isActiveInYear($year)) {
                    $daysInTheMonth[$dayOfTheMonthToCheck] = new RegularDay(
                        $dayOfTheMonthToCheck,
                        $numericRegularDayToDisplay,
                    );

                    ++$dayOfTheMonthToCheck;
                    ++$numericRegularDayToDisplay;

                    continue;
                }

                $daysInTheMonth[$dayOfTheMonthToCheck] = $leapDays[$dayOfTheMonthToCheck];
                ++$dayOfTheMonthToCheck;
                continue;
            }

            $daysInTheMonth[$dayOfTheMonthToCheck] = new RegularDay(
                $dayOfTheMonthToCheck,
                $numericRegularDayToDisplay,
            );

            ++$dayOfTheMonthToCheck;
            ++$numericRegularDayToDisplay;
        } while ($numericRegularDayToDisplay <= $this->amountOfRegularDays);

        // Finaly add leap days that are registered after the max amount of regular days
        foreach ($leapDays as $dayOfTheMonth => $leapDay) {
            if ($dayOfTheMonth <= $this->amountOfRegularDays) {
                continue;
            }

            // Ignore leap days that would lie in a gap
            $difference = $dayOfTheMonth - count($daysInTheMonth);
            if ($difference > 1) {
                continue;
            }

            $daysInTheMonth[$dayOfTheMonth] = $leapDay;
        }

        return $daysInTheMonth;
    }

    public function getDayInYear(int $day, int $year): Day
    {
        if ($this->leapDays === []) {
            // There are no leap days, so there will be no change based on the year
            return $this->getDay($day);
        }

        if (! isset($this->daysInMonthOfYear[$year])) {
            // The year is not cached, so create the cache
            $this->daysInMonthOfYear[$year] = $this->calculateDaysInTheMonth($this->leapDays, $year);
        }

        return $this->daysInMonthOfYear[$year][$day];
    }

    public function getDay(int $day): Day
    {
        return $this->daysInTheMonth[$day] ?? throw DayNotExistsInMonth::forDayGenerallyNotExistsInMonth($day);
    }

    public function countInYear(int $year): int
    {
        return count(array_filter(
            $this->daysInTheMonth,
            static fn (Day $day): bool => ! ($day instanceof LeapDay) || $day->isActiveInYear($year),
        ));
    }

    public function countLeapDaysInYear(int $year): int
    {
        return count(array_filter(
            $this->daysInTheMonth,
            static fn (Day $day): bool => $day instanceof LeapDay && $day->isActiveInYear($year),
        ));
    }

    public function count(): int
    {
        return count($this->daysInTheMonth);
    }

    public function countRegularDays(): int
    {
        return count(array_filter(
            $this->daysInTheMonth,
            static fn (Day $day): bool => $day instanceof RegularDay,
        ));
    }

    public function countLeapDaysUpToDayInYear(int $maxDay, int $year): int
    {
        $leapDays = array_filter(
            $this->daysInTheMonth,
            static fn (Day $day): bool => $day instanceof LeapDay && $day->isActiveInYear($year),
        );

        $leapDays = array_filter(
            $leapDays,
            static fn (Day $day): bool => $day->getDayOfTheMonth() <= $maxDay,
        );

        return count($leapDays);
    }

    /** @return LeapDay[] */
    public function getLeapDaysInYear(int $year): array
    {
        return array_filter(
            $this->daysInTheMonth,
            static fn (Day $day): bool => $day instanceof LeapDay && $day->isActiveInYear($year),
        );
    }

    /** @param array{regularDays: int<0, max>, leapDays?: array<array{day: int, name: string, yearInterval?: int}>} $data */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['regularDays'],
            ...array_map(
                LeapDay::fromArray(...),
                $data['leapDays'] ?? [],
            ),
        );
    }
}
