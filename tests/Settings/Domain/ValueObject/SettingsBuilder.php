<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\ValueObject;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Application;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Calendar;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotFunctions;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotGeneral;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotTuning;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Holiday;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\MoonCalendar;

class SettingsBuilder
{
    private Application $application;
    private ChatbotGeneral $chatbotGeneral;
    private ChatbotTuning $chatbotTuning;
    private ChatbotFunctions $chatbotFunctions;
    private Calendar $calendar;
    private MoonCalendar $moonCalendar;
    private Holiday $holiday;

    public function __construct()
    {
        $this->application      = new Application();
        $this->chatbotGeneral   = new ChatbotGeneral();
        $this->chatbotTuning    = new ChatbotTuning();
        $this->chatbotFunctions = new ChatbotFunctions();
        $this->calendar         = new Calendar();
        $this->moonCalendar     = new MoonCalendar();
        $this->holiday          = new Holiday();
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

    public function withCalendar(Calendar $calendar): self
    {
        $this->calendar = $calendar;

        return $this;
    }

    public function withMoonCalendar(MoonCalendar $moonCalendar): self
    {
        $this->moonCalendar = $moonCalendar;

        return $this;
    }

    public function withHoliday(Holiday $holiday): self
    {
        $this->holiday = $holiday;

        return $this;
    }

    public function build(): Settings
    {
        $settings = new Settings();
        $settings->setApplication($this->application);
        $settings->setChatbotGeneral($this->chatbotGeneral);
        $settings->setChatbotTuning($this->chatbotTuning);
        $settings->setChatbotFunctions($this->chatbotFunctions);
        $settings->setCalendar($this->calendar);
        $settings->setMoonCalendar($this->moonCalendar);
        $settings->setHoliday($this->holiday);

        return $settings;
    }
}
