<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\ToolBox\AsTool;

#[AsTool(
    'current_date',
    description: <<<'TEXT'
    Provides the current date in the role-playing game world. Use this function to get the exact current date (day,
    month, and year) as it is in the game universe. For understanding the structure of the calendar, such as the names
    of the months, the number of days in each month, or detailed inquiries about the calendar, consider using the
    "calendar" function. For understanding the phases of the moon or lunar-related events, use the "moon_calendar"
    function. For information on holidays and observances, refer to "calendar_holiday". If additional details about the
    calendar system, structure, or related events are requested, ensure to fetch these using the appropriate functions.

    Examples include:
    - Today's date: "What is the date today?"
    - Specific event timing: "What is the current date?"
    - Timeline alignment: "Where are we in the year?"

    Note: For questions about the broader calendar context, such as calculations with the calendar refer to the known
    functionalities to fetch additional information.
    TEXT,
)]
final class CurrentDate
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolUsageCollector $collector,
    ) {
    }

    public function __invoke(): string
    {
        $this->collector->called('current_date');

        return 'Heute ist der ' . $this->settingsHandler->get()->getCalendar()->getCurrentDate();
    }
}
