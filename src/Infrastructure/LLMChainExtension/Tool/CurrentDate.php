<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\LLMChainExtension\Tool;

use DZunke\NovDoc\Domain\Settings\SettingsHandler;
use PhpLlm\LlmChain\ToolBox\AsTool;

#[AsTool('current_date', description: 'Provides the current date and time.')]
final class CurrentDate
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function __invoke(): string
    {
        return 'Heute ist der ' . $this->settingsHandler->get()->getCalendar()->getCurrentDate();
    }
}
