<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\Service;

use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\ValueObject\LeapDay;

use function call_user_func;
use function sprintf;
use function str_contains;
use function str_replace;

final class DateFormatter
{
    private const array PLACEHOLDERS = [
        'd' => 'getDayNumber',
        'D' => 'getDayLabel',
        'm' => 'getMonthNumber',
        'M' => 'getMonthName',
        'y' => 'getYear',
        'Y' => 'getYearWithEpoch',
    ];

    public function format(CalendarDate $date, string $format): string
    {
        if ($this->isLeapDay($date)) {
            return $this->formatLeapDay($date);
        }

        $result = $format;
        foreach (self::PLACEHOLDERS as $placeholder => $method) {
            if (! str_contains($format, '%' . $placeholder)) {
                continue;
            }

            $value  = call_user_func([$this, $method], $date);
            $result = str_replace('%' . $placeholder, (string) $value, $result);
        }

        return $result;
    }

    private function isLeapDay(CalendarDate $date): bool
    {
        return $date->getDay() instanceof LeapDay;
    }

    private function formatLeapDay(CalendarDate $date): string
    {
        return sprintf(
            '%s %d %s',
            $date->getDay()->getLabel(),
            $date->getYear(),
            $date->getCalendar()->getEpochCollection()->getEpochForYear($date->getYear())->name,
        );
    }

    private function getDayNumber(CalendarDate $date): int
    {
        return (int) $date->getDay()->getLabel();
    }

    private function getDayLabel(CalendarDate $date): string
    {
        return $date->getDay()->getLabel();
    }

    private function getMonthNumber(CalendarDate $date): int
    {
        return $date->getMonth();
    }

    private function getMonthName(CalendarDate $date): string
    {
        return $date->getCalendar()->getMonth($date->getMonth())->name;
    }

    private function getYear(CalendarDate $date): int
    {
        return $date->getYear();
    }

    private function getYearWithEpoch(CalendarDate $date): string
    {
        return sprintf(
            '%d %s',
            $date->getYear(),
            $date->getCalendar()->getEpochCollection()->getEpochForYear($date->getYear())->name,
        );
    }
}
