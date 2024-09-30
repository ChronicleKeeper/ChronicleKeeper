<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\ToolBox\AsTool;

#[AsTool(
    'calendar',
    description: <<<'TEXT'
    Provides information about the calendar system in the role-playing game world. This includes details on the
    structure of the year, month names, the number of days in different cycles, and other relevant calendar
    information. Use this function for general calendar details and to understand the overall structure of the game's
    timeline. For specific dates, refer to "current_date". For holiday-related dates, refer to "calendar_holiday".
    Examples include:
    - Understanding the structure of the year: "How many days are in a year?"
    - Detailing month names: "What are the names of the months?"
    - Number of days in cycles: "How many days are in a month?"
    - General calendar questions: "What is a typical year like?"
    TEXT,
)]
final class Calendar
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolUsageCollector $collector,
    ) {
    }

    public function __invoke(): string
    {
        $this->collector->called('calendar');

        $furtherCalendarExplanation = $this->settingsHandler->get()->getCalendar()->getCalendarDescription();

        if ($furtherCalendarExplanation === '') {
            return 'Es gibt keine Informationen zum Kalender.';
        }

        return $furtherCalendarExplanation;
    }
}
