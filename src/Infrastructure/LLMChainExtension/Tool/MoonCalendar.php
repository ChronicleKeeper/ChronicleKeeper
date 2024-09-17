<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\LLMChainExtension\Tool;

use DZunke\NovDoc\Domain\Settings\SettingsHandler;
use PhpLlm\LlmChain\ToolBox\AsTool;

#[AsTool(
    'moon_calendar',
    description: <<<'TEXT'
    Liefert Informationen zu den Mondzyklen und dem gesamten Mondkalender in der Welt von Novalis. Für Fragen wie der
    Mond an einem spezfisichen Tag steht befrage auch die Funktion "current_date" nach dem aktuellen Datum.
    TEXT,
)]
final class MoonCalendar
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function __invoke(): string
    {
        $explanation = $this->settingsHandler->get()->getMoonCalendar()->getMoonCalendarDescription();

        if ($explanation === '') {
            return 'Es gibt keine Informationen zum Mondkalender.';
        }

        return $explanation;
    }
}
