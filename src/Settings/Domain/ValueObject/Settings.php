<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Application;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotFunctions;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotGeneral;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotTuning;

/**
 * @phpstan-import-type ApplicationSettings from Application
 * @phpstan-import-type ChatbotGeneralSettings from ChatbotGeneral
 * @phpstan-import-type ChatbotTuningArray from ChatbotTuning
 * @phpstan-import-type ChatbotFunctionsArray from ChatbotFunctions
 * @phpstan-import-type CalendarSettingsArray from CalendarSettings
 * @phpstan-type SettingsArray = array{
 *     application?: ApplicationSettings,
 *     chatbot: array{
 *         general: ChatbotGeneralSettings,
 *         tuning: ChatbotTuningArray,
 *         functions: ChatbotFunctionsArray
 *     },
 *     calendar_config?: CalendarSettingsArray
 * }
 */
class Settings
{
    private Application $application;
    private ChatbotGeneral $chatbotGeneral;
    private ChatbotTuning $chatbotTuning;
    private ChatbotFunctions $chatbotFunctions;
    private CalendarSettings $calendarSettings;

    public function __construct()
    {
        $this->application      = new Application();
        $this->chatbotGeneral   = new ChatbotGeneral();
        $this->chatbotTuning    = new ChatbotTuning();
        $this->chatbotFunctions = new ChatbotFunctions();
        $this->calendarSettings = new CalendarSettings();
    }

    /** @param SettingsArray $settingsArray */
    public static function fromArray(array $settingsArray): Settings
    {
        $settings = new Settings();
        $settings->setApplication(Application::fromArray(
            $settingsArray['application'] ?? ['open_ai_api_key' => ''],
        ));
        $settings->setChatbotGeneral(ChatbotGeneral::fromArray($settingsArray['chatbot']['general']));
        $settings->setChatbotTuning(ChatbotTuning::fromArray($settingsArray['chatbot']['tuning']));
        $settings->setChatbotFunctions(ChatbotFunctions::fromArray($settingsArray['chatbot']['functions']));

        if (isset($settingsArray['calendar_config'])) {
            $settings->setCalendarSettings(CalendarSettings::fromArray($settingsArray['calendar_config']));
        }

        return $settings;
    }

    /** @return SettingsArray */
    public function toArray(): array
    {
        return [
            'application' => $this->application->toArray(),
            'chatbot' => [
                'general' => $this->chatbotGeneral->toArray(),
                'tuning' => $this->chatbotTuning->toArray(),
                'functions' => $this->chatbotFunctions->toArray(),
            ],
            'calendar_config' => $this->calendarSettings->toArray(),
        ];
    }

    public function getApplication(): Application
    {
        return $this->application;
    }

    public function setApplication(Application $application): void
    {
        $this->application = $application;
    }

    public function getChatbotGeneral(): ChatbotGeneral
    {
        return $this->chatbotGeneral;
    }

    public function setChatbotGeneral(ChatbotGeneral $chatbotGeneral): void
    {
        $this->chatbotGeneral = $chatbotGeneral;
    }

    public function getChatbotTuning(): ChatbotTuning
    {
        return $this->chatbotTuning;
    }

    public function setChatbotTuning(ChatbotTuning $chatbotTuning): void
    {
        $this->chatbotTuning = $chatbotTuning;
    }

    public function getChatbotFunctions(): ChatbotFunctions
    {
        return $this->chatbotFunctions;
    }

    public function setChatbotFunctions(ChatbotFunctions $chatbotFunctions): void
    {
        $this->chatbotFunctions = $chatbotFunctions;
    }

    public function getCalendarSettings(): CalendarSettings
    {
        return $this->calendarSettings;
    }

    public function setCalendarSettings(CalendarSettings $calendarSettings): void
    {
        $this->calendarSettings = $calendarSettings;
    }
}
