<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Calendar\Application\Exception\CalendarConfigurationIncomplete;
use ChronicleKeeper\Calendar\Application\Query\GetCurrentDate;
use ChronicleKeeper\Calendar\Application\Query\LoadCalendar;
use ChronicleKeeper\Calendar\Application\Service\CalendarSettingsChecker;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar as CalendarEntity;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\Exception\MonthNotExists;
use ChronicleKeeper\Calendar\Domain\ValueObject\LeapDay;
use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

use function array_map;
use function implode;

use const PHP_EOL;

#[AsTool(
    'calendar_current_date',
    description: <<<'TEXT'
    Delivers calendar information about the current date. It contains the current state of the moon cycle and general
    information about the current date.
    TEXT,
)]
class CurrentDate
{
    public function __construct(
        private readonly QueryService $queryService,
        private readonly RuntimeCollector $runtimeCollector,
        private readonly CalendarSettingsChecker $calendarSettingsChecker,
    ) {
    }

    public function __invoke(): string
    {
        try {
            $this->calendarSettingsChecker->hasValidSettings();
        } catch (CalendarConfigurationIncomplete $e) {
            $result  = 'The calendar needs to be configured before utilizing this function.';
            $result .= PHP_EOL . $e->getMessage();

            $this->runtimeCollector->addFunctionDebug(
                new FunctionDebug(
                    tool: 'calendar_current_date',
                    result: $result,
                ),
            );

            return $result;
        }

        $calendar    = $this->queryService->query(new LoadCalendar());
        $currentDate = $this->queryService->query(new GetCurrentDate());

        $result  = $this->getCurrentDateInformation($currentDate);
        $result .= PHP_EOL . $this->getMoonCycleInformation($currentDate, $calendar);
        $result .= PHP_EOL;
        $result .= PHP_EOL . $this->getCurrentMonthInformation($currentDate, $calendar);
        $result .= PHP_EOL . $this->getCurrentMonthLeapDays($currentDate, $calendar);

        $this->runtimeCollector->addFunctionDebug(
            new FunctionDebug(
                tool: 'calendar_current_date',
                result: $result,
            ),
        );

        return $result;
    }

    private function getCurrentDateInformation(CalendarDate $currentDate): string
    {
        if ($currentDate->getDay() instanceof LeapDay) {
            return 'Today is a leap day. ' . $currentDate->format('%D in %M of year %Y');
        }

        return 'The current date is: ' . $currentDate->format('%w the %d in %M of year %Y');
    }

    private function getMoonCycleInformation(CalendarDate $currentDate, CalendarEntity $calendar): string
    {
        $moonCylce           = $calendar->getMoonCycle()->getMoonStateOfDay($currentDate)->getLabel();
        $currentMoonCycleDay = $calendar->getMoonCycle()->getDaysOfACycle($currentDate);
        $moonCylceTimeFrame  = $calendar->getMoonCycle()->getMoonCycle();

        return 'The current moon cycle state is ' . $moonCylce . ' on the ' . $currentMoonCycleDay . ' day within a complete cycle of ' . $moonCylceTimeFrame . ' days.';
    }

    private function getCurrentMonthInformation(CalendarDate $currentDate, CalendarEntity $calendar): string
    {
        $currentMonth         = $calendar->getMonth($currentDate->getMonth());
        $currentMonthDayCount = $currentMonth->days->countInYear($currentDate->getYear());

        try {
            $previousMonth     = $calendar->getMonth($currentDate->getMonth() - 1);
            $previousMonthYear = $currentDate->getYear();
        } catch (MonthNotExists) {
            $previousMonth     = $calendar->getMonth($calendar->getMonths()->count());
            $previousMonthYear = $currentDate->getYear() - 1;
        }

        try {
            $nextMonth     = $calendar->getMonth($currentDate->getMonth() + 1);
            $nextMonthYear = $currentDate->getYear();
        } catch (MonthNotExists) {
            $nextMonth     = $calendar->getMonth(1);
            $nextMonthYear = $currentDate->getYear() + 1;
        }

        return 'The current month is "' . $currentMonth->name . '" which has an amount of '
            . $currentMonthDayCount . ' days in the current year. The previous month was '
            . $previousMonth->name . ' in year ' . $previousMonthYear . ' and the next month will be '
            . $nextMonth->name . ' in year ' . $nextMonthYear . '.';
    }

    private function getCurrentMonthLeapDays(CalendarDate $currentDate, CalendarEntity $calendar): string
    {
        $currentMonth = $calendar->getMonth($currentDate->getMonth());
        $leapDays     = $currentMonth->days->getLeapDaysInYear($currentDate->getYear());

        if ($leapDays === []) {
            return 'The current month has no leap days.';
        }

        $leapDays = array_map(
            static fn (LeapDay $day) => $day->getLabel() . ' at day ' . $day->getDayOfTheMonth(),
            $leapDays,
        );

        return 'The current month has the following leap days: ' . implode(', ', $leapDays);
    }
}
