<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Domain\ValueObject;

use DZunke\NovDoc\Settings\Domain\ValueObject\Settings\Calendar;
use DZunke\NovDoc\Settings\Domain\ValueObject\Settings\ChatbotFunctions;
use DZunke\NovDoc\Settings\Domain\ValueObject\Settings\ChatbotGeneral;
use DZunke\NovDoc\Settings\Domain\ValueObject\Settings\ChatbotSystemPrompt;
use DZunke\NovDoc\Settings\Domain\ValueObject\Settings\ChatbotTuning;
use DZunke\NovDoc\Settings\Domain\ValueObject\Settings\Holiday;
use DZunke\NovDoc\Settings\Domain\ValueObject\Settings\MoonCalendar;

/**
 * @phpstan-import-type ChatbotGeneralSettings from ChatbotGeneral
 * @phpstan-import-type ChatbotSystemPromptSettings from ChatbotSystemPrompt
 * @phpstan-import-type ChatbotTuningArray from ChatbotTuning
 * @phpstan-import-type ChatbotFunctionsArray from ChatbotFunctions
 * @phpstan-import-type CalendarSettings from Calendar
 * @phpstan-import-type MoonCalendarSettings from MoonCalendar
 * @phpstan-import-type HolidaySettings from Holiday
 * @phpstan-type SettingsArray = array{
 *     chatbot: array{
 *         general: ChatbotGeneralSettings,
 *         system_prompt: ChatbotSystemPromptSettings,
 *         tuning: ChatbotTuningArray,
 *         functions: ChatbotFunctionsArray
 *     },
 *     calendar: array{
 *         general: CalendarSettings,
 *         moon: MoonCalendarSettings,
 *         holiday: HolidaySettings
 *     }
 * }
 */
class Settings
{
    private ChatbotGeneral $chatbotGeneral;
    private ChatbotSystemPrompt $chatbotSystemPrompt;
    private ChatbotTuning $chatbotTuning;
    private ChatbotFunctions $chatbotFunctions;
    private Calendar $calendar;
    private MoonCalendar $moonCalendar;
    private Holiday $holiday;

    public function __construct()
    {
        $this->chatbotGeneral      = new ChatbotGeneral();
        $this->chatbotSystemPrompt = new ChatbotSystemPrompt();
        $this->chatbotTuning       = new ChatbotTuning();
        $this->chatbotFunctions    = new ChatbotFunctions();
        $this->calendar            = new Calendar();
        $this->moonCalendar        = new MoonCalendar();
        $this->holiday             = new Holiday();
    }

    /** @param SettingsArray $settingsArray */
    public static function fromArray(array $settingsArray): Settings
    {
        $settings = new Settings();
        $settings->setChatbotGeneral(ChatbotGeneral::fromArray($settingsArray['chatbot']['general']));
        $settings->setChatbotSystemPrompt(ChatbotSystemPrompt::fromArray($settingsArray['chatbot']['system_prompt']));
        $settings->setChatbotTuning(ChatbotTuning::fromArray($settingsArray['chatbot']['tuning']));
        $settings->setChatbotFunctions(ChatbotFunctions::fromArray($settingsArray['chatbot']['functions']));
        $settings->setCalendar(Calendar::fromArray($settingsArray['calendar']['general']));
        $settings->setHoliday(Holiday::fromArray($settingsArray['calendar']['holiday']));
        $settings->setMoonCalendar(MoonCalendar::fromArray($settingsArray['calendar']['moon']));

        return $settings;
    }

    /** @return SettingsArray */
    public function toArray(): array
    {
        return [
            'chatbot' => [
                'general' => $this->chatbotGeneral->toArray(),
                'system_prompt' => $this->chatbotSystemPrompt->toArray(),
                'tuning' => $this->chatbotTuning->toArray(),
                'functions' => $this->chatbotFunctions->toArray(),
            ],
            'calendar' => [
                'general' => $this->calendar->toArray(),
                'moon' => $this->moonCalendar->toArray(),
                'holiday' => $this->holiday->toArray(),
            ],
        ];
    }

    public function getChatbotGeneral(): ChatbotGeneral
    {
        return $this->chatbotGeneral;
    }

    public function setChatbotGeneral(ChatbotGeneral $chatbotGeneral): void
    {
        $this->chatbotGeneral = $chatbotGeneral;
    }

    public function getChatbotSystemPrompt(): ChatbotSystemPrompt
    {
        return $this->chatbotSystemPrompt;
    }

    public function setChatbotSystemPrompt(ChatbotSystemPrompt $chatbotSystemPrompt): void
    {
        $this->chatbotSystemPrompt = $chatbotSystemPrompt;
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

    public function getCalendar(): Calendar
    {
        return $this->calendar;
    }

    public function setCalendar(Calendar $calendar): void
    {
        $this->calendar = $calendar;
    }

    public function getMoonCalendar(): MoonCalendar
    {
        return $this->moonCalendar;
    }

    public function setMoonCalendar(MoonCalendar $moonCalendar): void
    {
        $this->moonCalendar = $moonCalendar;
    }

    public function getHoliday(): Holiday
    {
        return $this->holiday;
    }

    public function setHoliday(Holiday $holiday): void
    {
        $this->holiday = $holiday;
    }
}
