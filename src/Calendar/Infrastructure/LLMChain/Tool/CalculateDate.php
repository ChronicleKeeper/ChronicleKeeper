<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Calendar\Application\Exception\CalendarConfigurationIncomplete;
use ChronicleKeeper\Calendar\Application\Query\GetCurrentDate;
use ChronicleKeeper\Calendar\Application\Query\LoadCalendar;
use ChronicleKeeper\Calendar\Application\Service\CalendarSettingsChecker;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar as CalendarEntity;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use Throwable;

use function floor;
use function strtolower;
use function trim;

use const PHP_EOL;

#[AsTool(
    'calendar_date_calculator',
    description: <<<'TEXT'
    IMPORTANT: BEFORE using this tool, you MUST first call calendar_system_info to understand the fantasy calendar structure!

    Performs date calculations in a CUSTOM FANTASY CALENDAR system that differs from Earth's calendar.
    This tool requires understanding the unique calendar structure (days per week, months per year, etc.)
    which can only be obtained by first calling calendar_system_info.

    Operations:
    - "add_days": Adds days to current date (requires days parameter)
    - "subtract_days": Subtracts days from current date (requires days parameter)
    - "days_until_next_moon_phase": Days until specified moon phase (requires phase parameter)
    - "days_between": Days between current date and a specified date (requires year, month, day parameters)

    Example usage:
    calendar_date_calculator("add_days", 7) - What date will it be 7 days from now
    calendar_date_calculator("days_until_next_moon_phase", "Full Moon") - Days until next full moon
    TEXT,
)]
class CalculateDate
{
    public function __construct(
        private readonly QueryService $queryService,
        private readonly RuntimeCollector $runtimeCollector,
        private readonly CalendarSettingsChecker $calendarSettingsChecker,
    ) {
    }

    /**
     * @param string      $operation One of "add_days", "subtract_days", "days_until_next_moon_phase", "days_between".
     * @param int|null    $days      An amount of days for addition or subtraction of days.
     * @param string|null $phase     Which moon phase to calculate days until.
     * @param int|null    $year      A year for the date between calculation.
     * @param int|null    $month     A month for the date between calculation.
     * @param int|null    $day       A day for the date between calculation.
     */
    public function __invoke(
        string $operation,
        int|null $days = null,
        string|null $phase = null,
        int|null $year = null,
        int|null $month = null,
        int|null $day = null,
    ): string {
        $debugArguments = [
            'operation' => $operation,
            'days' => $days,
            'phase' => $phase,
            'year' => $year,
            'month' => $month,
            'day' => $day,
        ];

        try {
            $this->calendarSettingsChecker->hasValidSettings();
        } catch (CalendarConfigurationIncomplete $e) {
            $result = '[ERROR] The calendar needs to be configured before utilizing this function.' . PHP_EOL . $e->getMessage();

            $this->runtimeCollector->addFunctionDebug(
                new FunctionDebug(
                    tool: 'calendar_date_calculator',
                    arguments: $debugArguments,
                    result: $result,
                ),
            );

            return $result;
        }

        $calendar    = $this->queryService->query(new LoadCalendar());
        $currentDate = $this->queryService->query(new GetCurrentDate());

        $result = match ($operation) {
            'add_days' => $this->addDays($currentDate, $calendar, $days),
            'subtract_days' => $this->subtractDays($currentDate, $calendar, $days),
            'days_until_next_moon_phase' => $this->daysUntilNextMoonPhase($currentDate, $calendar, $phase),
            'days_between' => $this->daysBetween($currentDate, $calendar, $year, $month, $day),
            default => '[ERROR] Unknown operation. Valid operations are: add_days, subtract_days, days_until_next_moon_phase, days_between.'
        };

        $this->runtimeCollector->addFunctionDebug(
            new FunctionDebug(
                tool: 'calendar_date_calculator',
                arguments: $debugArguments,
                result: $result,
            ),
        );

        return $result;
    }

    private function addDays(CalendarDate $currentDate, CalendarEntity $calendar, int|null $days): string
    {
        if ($days === null || $days < 1) {
            return '[ERROR] The days parameter must be a positive number.';
        }

        $futureDate = $currentDate->addDays($days);

        $summary  = '[DATE CALCULATION] Adding ' . $days . ' days to the current date:' . PHP_EOL;
        $summary .= '- Current date: ' . $this->formatDate($currentDate) . PHP_EOL;
        $summary .= '- Result date: ' . $this->formatDate($futureDate) . PHP_EOL;

        // Additional information about the result date
        $summary .= PHP_EOL . 'Information about the result date:' . PHP_EOL;
        $summary .= '- Moon phase: ' . $calendar->getMoonCycle()->getMoonStateOfDay($futureDate)->getLabel() . PHP_EOL;
        $summary .= '- Month days: ' . $calendar->getMonth($futureDate->getMonth())->days->countInYear($futureDate->getYear()) . PHP_EOL;

        return $summary . ('- Weekday: ' . ($futureDate->getWeekDay()->name ?? 'N/A') . PHP_EOL);
    }

    private function subtractDays(CalendarDate $currentDate, CalendarEntity $calendar, int|null $days): string
    {
        if ($days === null || $days < 1) {
            return '[ERROR] The days parameter must be a positive number.';
        }

        $pastDate = $currentDate->subDays($days);

        $summary  = '[DATE CALCULATION] Subtracting ' . $days . ' days from the current date:' . PHP_EOL;
        $summary .= '- Current date: ' . $this->formatDate($currentDate) . PHP_EOL;
        $summary .= '- Result date: ' . $this->formatDate($pastDate) . PHP_EOL;

        // Additional information about the result date
        $summary .= PHP_EOL . 'Information about the result date:' . PHP_EOL;
        $summary .= '- Moon phase: ' . $calendar->getMoonCycle()->getMoonStateOfDay($pastDate)->getLabel() . PHP_EOL;
        $summary .= '- Month days: ' . $calendar->getMonth($pastDate->getMonth())->days->countInYear($pastDate->getYear()) . PHP_EOL;

        return $summary . ('- Weekday: ' . ($pastDate->getWeekDay()->name ?? 'N/A') . PHP_EOL);
    }

    private function daysUntilNextMoonPhase(
        CalendarDate $currentDate,
        CalendarEntity $calendar,
        string|null $phase,
    ): string {
        if ($phase === null || trim($phase) === '') {
            return '[ERROR] You must specify a moon phase to calculate days until.';
        }

        $moonCycle    = $calendar->getMoonCycle();
        $currentPhase = $moonCycle->getMoonStateOfDay($currentDate)->getLabel();
        $daysInCycle  = $moonCycle->getMoonCycle();

        // This is a simplified calculation - you would need to implement the actual logic
        // based on your moon cycle implementation
        $daysUntil  = 0;
        $targetDate = clone $currentDate;
        $maxDays    = $daysInCycle * 2; // Prevent infinite loop
        $found      = false;

        for ($i = 1; $i <= $maxDays; $i++) {
            $targetDate = $targetDate->addDays(1);
            $nextPhase  = $moonCycle->getMoonStateOfDay($targetDate)->getLabel();

            if (strtolower($nextPhase) === strtolower($phase)) {
                $daysUntil = $i;
                $found     = true;
                break;
            }
        }

        if (! $found) {
            return '[ERROR] Could not find the specified moon phase "' . $phase
                . '". Valid phases in this calendar might be different.';
        }

        $summary  = '[MOON PHASE CALCULATION] Days until next "' . $phase . '":' . PHP_EOL;
        $summary .= '- Current date: ' . $this->formatDate($currentDate) . PHP_EOL;
        $summary .= '- Current moon phase: ' . $currentPhase . PHP_EOL;
        $summary .= '- Days until next "' . $phase . '": ' . $daysUntil . PHP_EOL;

        return $summary . ('- Date of next "' . $phase . '": ' . $this->formatDate($targetDate) . PHP_EOL);
    }

    private function daysBetween(
        CalendarDate $currentDate,
        CalendarEntity $calendar,
        int|null $year,
        int|null $month,
        int|null $day,
    ): string {
        if ($year === null || $month === null || $day === null) {
            return '[ERROR] You must specify year, month, and day parameters for days_between operation.';
        }

        try {
            $targetDate = new CalendarDate($calendar, $year, $month, $day);

            $daysDiff     = $currentDate->diffInDays($targetDate);
            $leapDayCount = $currentDate->countLeapDaysBetween($targetDate);

            $summary  = '[DATE DIFFERENCE CALCULATION]' . PHP_EOL;
            $summary .= '- First date: ' . $this->formatDate($currentDate) . PHP_EOL;
            $summary .= '- Second date: ' . $this->formatDate($targetDate) . PHP_EOL;
            $summary .= '- Days between: ' . $daysDiff . ' (including ' . $leapDayCount . ' leap days)' . PHP_EOL;

            if ($daysDiff > 0) {
                $weeksCounts = floor($daysDiff / $calendar->getWeeks()->countDays());
                $summary    .= '- This represents approximately ' . $weeksCounts . ' weeks in this calendar' . PHP_EOL;

                $moonCycles = floor($daysDiff / $calendar->getMoonCycle()->getMoonCycle());
                $summary   .= '- This represents approximately ' . $moonCycles . ' complete moon cycles' . PHP_EOL;
            }

            return $summary;
        } catch (Throwable $e) {
            return '[ERROR] Could not calculate days between dates: ' . $e->getMessage();
        }
    }

    private function formatDate(CalendarDate $date): string
    {
        $weekdayName = $date->getWeekDay()->name ?? '';
        $dateFormat  = $weekdayName !== '' ? $weekdayName . ', %d %M %Y' : '%d %M %Y';

        return $date->format($dateFormat);
    }
}
