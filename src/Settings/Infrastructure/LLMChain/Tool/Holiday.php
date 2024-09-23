<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Infrastructure\LLMChain\Tool;

use DZunke\NovDoc\Settings\Application\SettingsHandler;
use DZunke\NovDoc\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\ToolBox\AsTool;

#[AsTool(
    'calendar_holiday',
    description: <<<'TEXT'
    Diese Funktion liefert Informationen über die Feiertage in Novalis. Für die Beziehung der Feiertage zum Kalender
    kann die Funktion "calendar" zusätzliche Informationen ausgeben. Genauso kann die Funktion "current_date" helfen
    die Position der Feiertage zum aktuellen Datum herauszufinden.
    TEXT,
)]
final class Holiday
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolUsageCollector $collector,
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
