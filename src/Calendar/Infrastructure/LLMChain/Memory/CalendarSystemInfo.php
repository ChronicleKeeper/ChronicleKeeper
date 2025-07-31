<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Infrastructure\LLMChain\Memory;

use ChronicleKeeper\Calendar\Application\Query\LoadCalendar;
use ChronicleKeeper\Calendar\Application\Service\CalendarSettingsChecker;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\ValueObject\MoonState;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\Memory\Memory;
use PhpLlm\LlmChain\Chain\Memory\MemoryProviderInterface;

use function array_map;
use function assert;
use function implode;

use const PHP_EOL;

final class CalendarSystemInfo implements MemoryProviderInterface
{
    public function __construct(
        private readonly QueryService $queryService,
        private readonly CalendarSettingsChecker $calendarSettingsChecker,
    ) {
    }

    /** @inheritdoc */
    public function loadMemory(Input $input): array
    {
        if ($this->calendarSettingsChecker->hasValidSettings(false) === false) {
            return [];
        }

        $calendar = $this->queryService->query(new LoadCalendar());
        assert($calendar instanceof Calendar);

        $info = '## Fantasy Calendar Instructions' . PHP_EOL . PHP_EOL;

        // Week structure
        $info .= 'WEEK STRUCTURE:' . PHP_EOL;
        $info .= '- Days per week: ' . $calendar->getWeeks()->countDays() . PHP_EOL;
        $info .= '- Day names: ' . implode(
            ', ',
            array_map(static fn ($day) => $day->name, $calendar->getWeeks()->getDays()),
        ) . PHP_EOL . PHP_EOL;

        // Month structure
        $info .= 'MONTH STRUCTURE:' . PHP_EOL;
        $info .= '- Months per year: ' . $calendar->getMonths()->count() . PHP_EOL;
        foreach ($calendar->getMonths()->getAll() as $month) {
            $info .= '  • ' . $month->name . ': ' . $month->days->countRegularDays() . ' regular days';

            $leapDayCount = $month->days->count() - $month->days->countRegularDays();
            if ($leapDayCount > 0) {
                $info .= ', plus ' . $leapDayCount . ' potential leap days';
            }

            $info .= PHP_EOL;
        }

        $info .= PHP_EOL;

        // Moon cycle
        $info .= 'MOON CYCLE:' . PHP_EOL;
        $info .= '- Complete cycle: ' . $calendar->getMoonCycle()->getMoonCycle() . ' days' . PHP_EOL;
        $info .= '- Moon phases: ' . implode(
            ' → ',
            array_map(static fn (MoonState $phase) => $phase->getLabel(), MoonState::cases()),
        ) . PHP_EOL . PHP_EOL;

        // Leap day rules
        $info .= 'LEAP DAY RULES:' . PHP_EOL;
        // Add your leap day calculation rules here
        $info .= PHP_EOL;

        // Important notes for the LLM
        $info .= 'IMPORTANT NOTES FOR AI:' . PHP_EOL;
        $info .= "- This is a fantasy calendar that doesn't match Earth's calendar" . PHP_EOL;
        $info .= '- DO NOT assume a 7-day week or 12-month year' . PHP_EOL;
        $info .= "- Always specify you're using this fantasy calendar when mentioning dates" . PHP_EOL;
        $info .= '- For date calculations, use the calendar_date_calculator tool' . PHP_EOL;

        return [new Memory($info)];
    }
}
