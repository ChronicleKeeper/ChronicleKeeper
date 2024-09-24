<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_readable;
use function json_decode;
use function json_encode;
use function json_validate;
use function unlink;

use const JSON_PRETTY_PRINT;

class SettingsHandler
{
    private bool $loaded = false;
    private Settings $settings;

    public function __construct(
        private readonly string $settingsFilePath,
    ) {
        $this->settings = new Settings();
    }

    public function get(): Settings
    {
        if ($this->loaded === false) {
            $this->loadSettings();
        }

        return $this->settings;
    }

    public function store(): void
    {
        file_put_contents($this->settingsFilePath, json_encode($this->settings->toArray(), JSON_PRETTY_PRINT));
    }

    public function reset(): void
    {
        if (! file_exists($this->settingsFilePath)) {
            return;
        }

        unlink($this->settingsFilePath);

        $this->loaded   = false;
        $this->settings = new Settings();
    }

    private function loadSettings(): void
    {
        if (! file_exists($this->settingsFilePath) || ! is_readable($this->settingsFilePath)) {
            return;
        }

        $settingsFileContent = file_get_contents($this->settingsFilePath);
        if ($settingsFileContent === false || ! json_validate($settingsFileContent)) {
            return;
        }

        $settingsArr = json_decode($settingsFileContent, true);
        if (! is_array($settingsArr)) {
            return;
        }

        $this->settings = Settings::fromArray($settingsArr); // @phpstan-ignore argument.type
        $this->loaded   = true;
    }
}
