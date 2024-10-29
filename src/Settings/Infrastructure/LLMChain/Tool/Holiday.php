<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\ToolBox\Attribute\AsTool;

#[AsTool(
    'calendar_holiday',
    description: <<<'TEXT'
    Provides information about holidays in the role-playing game world. Use this function to get details about specific
    holidays and their dates. To understand how holidays fit into the broader calendar, refer to "calendar". For the
    current date, use "current_date".
    Examples include:
    - Specific holiday details: "What holidays are celebrated this month?"
    - Understanding holiday observances: "What are the major holidays in this world?"
    - Aligning holidays in the calendar: "When is the next festival?"
    TEXT,
)]
final readonly class Holiday
{
    public function __construct(
        private SettingsHandler $settingsHandler,
        private ToolUsageCollector $collector,
    ) {
    }

    public function __invoke(): string
    {
        $this->collector->called('calendar_holiday');

        $explanation = $this->settingsHandler->get()->getHoliday()->getDescription();

        if ($explanation === '') {
            return 'Es gibt keine Informationen zum Kalender.';
        }

        return $explanation;
    }
}
