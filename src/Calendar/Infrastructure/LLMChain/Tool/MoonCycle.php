<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Calendar\Application\Exception\CalendarConfigurationIncomplete;
use ChronicleKeeper\Calendar\Application\Query\GetCurrentDate;
use ChronicleKeeper\Calendar\Application\Query\LoadCalendar;
use ChronicleKeeper\Calendar\Application\Service\CalendarSettingsChecker;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;

use function array_reverse;
use function assert;
use function ceil;

use const PHP_EOL;

#[AsTool(
    'calendar_moon_cycle',
    description: <<<'TEXT'
    Provides detailed information about upcoming moon phases. Use this tool to find the next occurrence of
    a specific moon phase (new moon, full moon, etc.) or to list all upcoming moon phases for a given time period.
    Examples:
    - To find the next full moon date
    - To determine when the next new moon will occur
    - To list all moon phases in the coming month
    - To find the last full moon date
    - to determine when the last new moon occurred
    - To list all moon phases in the last month
    TEXT,
)]
class MoonCycle
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
                    tool: 'calendar_moon_cycle',
                    result: $result,
                ),
            );

            return $result;
        }

        $calendar    = $this->queryService->query(new LoadCalendar());
        $currentDate = $this->queryService->query(new GetCurrentDate());

        assert($calendar instanceof Calendar);
        $moonCycleTimeFrame  = $calendar->getMoonCycle()->getMoonCycle();
        $currentMoonCycleDay = $calendar->getMoonCycle()->getDaysOfACycle($currentDate);

        $result  = '## Current Moon Cycle Information' . PHP_EOL . PHP_EOL;
        $result .= '**Current Phase:** ' . $calendar->getMoonCycle()->getMoonStateOfDay($currentDate)->getLabel() . PHP_EOL;
        $result .= '**Current Day in Cycle:** Day ' . $currentMoonCycleDay . ' of ' . $moonCycleTimeFrame . PHP_EOL . PHP_EOL;

        $result .= '### Days in the past for questions to past moon cycle information' . PHP_EOL . PHP_EOL;
        $result .= $this->formattingMoonCycleDayList(
            $this->getPastMoonCycleDays($currentDate, $calendar),
            $calendar,
        );

        $result .= PHP_EOL . PHP_EOL;
        $result .= '### Days in the future for questions for future moon cycle information' . PHP_EOL . PHP_EOL;
        $result .= $this->formattingMoonCycleDayList(
            $this->getUpcomingMoonCycleDays($currentDate, $calendar),
            $calendar,
        );

        $this->runtimeCollector->addFunctionDebug(
            new FunctionDebug(
                tool: 'calendar_moon_cycle',
                result: $result,
            ),
        );

        return $result;
    }

    /** @param CalendarDate[] $days */
    private function formattingMoonCycleDayList(array $days, Calendar $calendar): string
    {
        $result = '';
        foreach ($days as $day) {
            $moonCycle     = $calendar->getMoonCycle()->getMoonStateOfDay($day)->getLabel();
            $formattedDate = $day->format('%D in %M of year %Y');

            $result .= '- **' . $formattedDate . '**: ' . $moonCycle . PHP_EOL;
        }

        return $result;
    }

    /** @return list<CalendarDate> */
    private function getUpcomingMoonCycleDays(CalendarDate $currentDate, Calendar $calendar): array
    {
        $moonCycleTimeFrame   = (int) ceil($calendar->getMoonCycle()->getMoonCycle());
        $moonCycleEndDate     = $currentDate->addDays($moonCycleTimeFrame);
        $moonCycleDayToHandle = $currentDate->addDays(1);

        $days = [];
        do {
            $days[] = $moonCycleDayToHandle;

            $moonCycleDayToHandle = $moonCycleDayToHandle->addDays(1);
        } while ($moonCycleDayToHandle->isSame($moonCycleEndDate) === false);

        return $days;
    }

    /** @return list<CalendarDate> */
    private function getPastMoonCycleDays(CalendarDate $currentDate, Calendar $calendar): array
    {
        $moonCycleTimeFrame   = (int) ceil($calendar->getMoonCycle()->getMoonCycle());
        $moonCycleEndDate     = $currentDate->subDays($moonCycleTimeFrame);
        $moonCycleDayToHandle = $currentDate->subDays(1);

        $days = [];
        do {
            $days[] = $moonCycleDayToHandle;

            $moonCycleDayToHandle = $moonCycleDayToHandle->subDays(1);
        } while ($moonCycleDayToHandle->isSame($moonCycleEndDate) === false);

        return array_reverse($days);
    }
}
