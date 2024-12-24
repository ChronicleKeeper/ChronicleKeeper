<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

#[AsTool(
    'moon_calendar',
    description: <<<'TEXT'
    Provides information about the moon cycles and the entire lunar calendar in the role-playing game world. This is
    useful for understanding phases of the moon, their effects, and related events. For explicit date-related
    information, consider referencing the "calendar" and "calendar_holiday" functions. For the current date,
    refer to "current_date".
    Examples include:
    - Current moon phase: "What phase is the moon in today?"
    - Detailed moon cycles: "What are the phases of the moon?"
    - Calendar alignment: "How does the lunar calendar align with the regular calendar?"
    - Specific moon-related events: "When is the next lunar eclipse?"
    TEXT,
)]
final readonly class MoonCalendar
{
    public function __construct(
        private SettingsHandler $settingsHandler,
        private RuntimeCollector $collector,
    ) {
    }

    public function __invoke(): string
    {
        $explanation = $this->settingsHandler->get()->getMoonCalendar()->getMoonCalendarDescription();

        if ($explanation === '') {
            $this->collector->addFunctionDebug(new FunctionDebug(tool: 'moon_calendar'));

            return 'Es gibt keine Informationen zum Mondkalender.';
        }

        $this->collector->addFunctionDebug(new FunctionDebug(tool: 'moon_calendar', result: $explanation));

        return $explanation;
    }
}
