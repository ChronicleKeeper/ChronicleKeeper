<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Migrator\Setting;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Migrator\FileMigration;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Application;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Dotenv\Dotenv;
use Throwable;

use function assert;
use function version_compare;

final class AddOpenAiKeyToSettings implements FileMigration
{
    public function __construct(
        private readonly FileAccess $filesystem,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function isSupporting(FileType $type, string $fileVersion): bool
    {
        return $type === FileType::DOTENV && version_compare($fileVersion, '0.5', '<');
    }

    public function migrate(string $file, FileType $type): void
    {
        assert($file !== '');

        try {
            $dotEnvContent = $this->filesystem->read('general.project', $file);
        } catch (Throwable) {
            return;
        }

        $dotEnvContent = (new Dotenv())->parse($dotEnvContent);
        if (! isset($dotEnvContent['OPENAI_API_KEY'])) {
            return;
        }

        $settings = $this->settingsHandler->get();
        $settings->setApplication(new Application(openAIApiKey: $dotEnvContent['OPENAI_API_KEY']));

        $this->settingsHandler->store();
    }
}
