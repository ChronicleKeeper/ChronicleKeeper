<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\LLMExtension\Tool;

use DZunke\NovDoc\Domain\Settings\SettingsHandler;
use PhpLlm\LlmChain\ToolBox\AsTool;

#[AsTool('clock', description: 'Provides the current date and time.')]
final class CurrentDateTime
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function __invoke(): string
    {
        // Get Current Date from application setting to make it changeable by the user!
        // return 'Today is the 26. arthan of the cycle 1262';
        return 'Es ist der ' . $this->settingsHandler->get()->currentDate;
    }
}
