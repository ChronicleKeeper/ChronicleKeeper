<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\ValueObject;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Application;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotFunctions;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotGeneral;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotTuning;

class SettingsBuilder
{
    private Application $application;
    private ChatbotGeneral $chatbotGeneral;
    private ChatbotTuning $chatbotTuning;
    private ChatbotFunctions $chatbotFunctions;

    public function __construct()
    {
        $this->application      = new Application();
        $this->chatbotGeneral   = new ChatbotGeneral();
        $this->chatbotTuning    = new ChatbotTuning();
        $this->chatbotFunctions = new ChatbotFunctions();
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

    public function build(): Settings
    {
        $settings = new Settings();
        $settings->setApplication($this->application);
        $settings->setChatbotGeneral($this->chatbotGeneral);
        $settings->setChatbotTuning($this->chatbotTuning);
        $settings->setChatbotFunctions($this->chatbotFunctions);

        return $settings;
    }
}
