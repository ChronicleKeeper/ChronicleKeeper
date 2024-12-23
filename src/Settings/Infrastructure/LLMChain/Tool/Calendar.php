<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

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
final readonly class Calendar
{
    public function __construct(
        private SettingsHandler $settingsHandler,
        private RuntimeCollector $collector,
    ) {
    }

    public function __invoke(): string
    {
        $furtherCalendarExplanation = $this->settingsHandler->get()->getCalendar()->getCalendarDescription();

        if ($furtherCalendarExplanation === '') {
            $this->collector->addFunctionDebug(new FunctionDebug(tool: 'calendar'));

            return 'Es gibt keine Informationen zum Kalender.';
        }

        $this->collector->addFunctionDebug(new FunctionDebug(tool: 'calendar', result: $furtherCalendarExplanation));

        return $furtherCalendarExplanation;
    }
}
