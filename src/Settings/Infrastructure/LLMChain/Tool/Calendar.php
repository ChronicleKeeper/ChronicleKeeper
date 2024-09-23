<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Infrastructure\LLMChain\Tool;

use DZunke\NovDoc\Settings\Application\SettingsHandler;
use DZunke\NovDoc\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\ToolBox\AsTool;

#[AsTool(
    'calendar',
    description: <<<'TEXT'
    Diese Funktion liefert Kalenderinformationen zur Welt von Novalis. Sie stellt Informationen bereit um Fragen wie zum
    Beispiel "Welcher Tag ist nächste Woche?" oder "Welche Monate gibt es?" sollten mit dieser Funktion beantwortet werden.
    Es kann auch hilfreich sein sie zu verwenden, wenn generell Fragen zum Kalendersystem aufkommen wie zum Beispiel
    "Wie viele Tage hat ein Jahr?", "Wie viele Tage hat ein Zyklus?" oder "Wie viele Tage hat ein Monat?". Verwende die
    Funktion "current_date" um das aktuelle Datum zu erhalten. Auch "Welche weiteren Monate gibt es?" könnte eine Frage sein
    die du hier beantwortet bekommst.
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
