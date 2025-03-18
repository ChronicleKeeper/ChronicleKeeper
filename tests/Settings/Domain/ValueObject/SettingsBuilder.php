<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\ValueObject;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Application;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\LeapDaySettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotFunctions;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotGeneral;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotTuning;

class SettingsBuilder
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

    public function withApplication(Application $application): self
    {
        $this->application = $application;

        return $this;
    }

    public function withChatbotGeneral(ChatbotGeneral $chatbotGeneral): self
    {
        $this->chatbotGeneral = $chatbotGeneral;

        return $this;
    }

    public function withChatbotTuning(ChatbotTuning $chatbotTuning): self
    {
        $this->chatbotTuning = $chatbotTuning;

        return $this;
    }

    public function withChatbotFunctions(ChatbotFunctions $chatbotFunctions): self
    {
        $this->chatbotFunctions = $chatbotFunctions;

        return $this;
    }

    public function withCalendarSettings(CalendarSettings $calendarSettings): self
    {
        $this->calendarSettings = $calendarSettings;

        return $this;
    }

    public function withDefaultCalendarSettings(): self
    {
        $leapDay = new LeapDaySettings(29, 'Leap Day', 4);
        $month   = new MonthSettings(1, 'First Month', 31, [$leapDay]);
        $epoch   = new EpochSettings('First Age', 0, null);
        $week    = new WeekSettings(1, 'First Day');

        $this->calendarSettings = new CalendarSettings(
            'Mond',
            30,
            0.0,
            true,
            [$month],
            [$epoch],
            [$week],
            new CurrentDay(1, 1, 1),
        );

        return $this;
    }

    public function build(): Settings
    {
        $settings = new Settings();
        $settings->setApplication($this->application);
        $settings->setChatbotGeneral($this->chatbotGeneral);
        $settings->setChatbotTuning($this->chatbotTuning);
        $settings->setChatbotFunctions($this->chatbotFunctions);
        $settings->setCalendarSettings($this->calendarSettings);

        return $settings;
    }
}
