<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;

use function is_array;
use function json_decode;
use function json_encode;
use function json_validate;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class SettingsHandler
{
    private bool $loaded = false;
    private Settings $settings;

    public function __construct(
        private readonly FileAccess $fileAccess,
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
        $this->fileAccess->write(
            'storage',
            'settings.json',
            json_encode($this->settings->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
        );
    }

    private function exists(): bool
    {
        return $this->fileAccess->exists('storage', 'settings.json');
    }

    public function reset(): void
    {
        if (! $this->exists()) {
            return;
        }

        $this->fileAccess->delete('storage', 'settings.json');

        $this->loaded   = false;
        $this->settings = new Settings();
    }

    private function loadSettings(): void
    {
        if (! $this->exists()) {
            return;
        }

        $settingsFileContent = $this->fileAccess->read('storage', 'settings.json');
        if ($settingsFileContent === '' || ! json_validate($settingsFileContent)) {
            return;
        }

        $settingsArr = json_decode($settingsFileContent, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($settingsArr)) {
            return;
        }

        $this->settings = Settings::fromArray($settingsArr); // @phpstan-ignore argument.type
        $this->loaded   = true;
    }
}
