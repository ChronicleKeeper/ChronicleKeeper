<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\ToolBox\AsTool;

#[AsTool('current_date', description: 'Provides the current date and time.')]
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
