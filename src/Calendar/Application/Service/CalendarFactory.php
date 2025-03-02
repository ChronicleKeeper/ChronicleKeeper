<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Application\Service;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\MoonCycle;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;

final class CalendarFactory
{
    public function createFromSettings(CalendarSettings $settings): Calendar
    {
        // Create month data structure for Calendar Entity
        $monthsData = $this->transformMonthsData($settings->getMonths());

        // Create epochs data structure for Calendar Entity
        $epochsData = $this->transformEpochsData($settings->getEpochs());

        // Create week days data structure for Calendar Entity
        $weekDaysData = $this->transformWeekDaysData($settings->getWeeks());

        // Create moon cycle
        $moonCycle = new MoonCycle($settings->getMoonCycleDays());

        // Create configuration
        $configuration = $this->createConfiguration();

        return new Calendar(
            $configuration,
            $monthsData,
            $epochsData,
            $weekDaysData,
            $moonCycle,
        );
    }

    /**
     * @param array<MonthSettings> $months
     *
     * @return list<array{index: int, name: string, days: int<0, max>, leapDays?: array<array{day: int, name: string, yearInterval?: int}>}>
     */
    private function transformMonthsData(array $months): array
    {
        $result = [];

        foreach ($months as $month) {
            $monthData = [
                'index' => $month->getIndex(),
                'name' => $month->getName(),
                'days' => $month->getDays(),
            ];

            $formattedLeapDays = [];
            foreach ($month->getLeapDays() as $leapDay) {
                $leapDayData = [
                    'day' => $leapDay->getDay(),
                    'name' => $leapDay->getName(),
                ];

                if ($leapDay->getYearInterval() !== null) {
                    $leapDayData['yearInterval'] = $leapDay->getYearInterval();
                }

                $formattedLeapDays[] = $leapDayData;
            }

            $monthData['leapDays'] = $formattedLeapDays;

            $result[] = $monthData;
        }

        return $result;
    }

    /**
     * @param array<EpochSettings> $epochs
     *
     * @return array<array{name: string, startYear: int<0, max>, endYear?: int<0, max>|null}>
     */
    private function transformEpochsData(array $epochs): array
    {
        $result = [];

        foreach ($epochs as $epoch) {
            $epochData = [
                'name' => $epoch->getName(),
                'startYear' => $epoch->getStartYear(),
            ];

            $endYear = $epoch->getEndYear();
            if ($endYear !== null) {
                $epochData['endYear'] = $endYear;
            }

            $result[] = $epochData;
        }

        return $result;
    }

    /**
     * @param array<WeekSettings> $weeks
     *
     * @return array<array{index: int, name: string}>
     */
    private function transformWeekDaysData(array $weeks): array
    {
        $result = [];

        foreach ($weeks as $week) {
            $result[] = [
                'index' => $week->getIndex(),
                'name' => $week->getName(),
            ];
        }

        return $result;
    }

    private function createConfiguration(): Configuration
    {
        // Default configuration values
        // You might want to adjust these or make them configurable in settings
        return new Configuration(beginsInYear: 0);
    }
}
