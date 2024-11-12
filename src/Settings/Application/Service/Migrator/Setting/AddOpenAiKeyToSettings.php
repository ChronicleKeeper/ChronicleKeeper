<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Migrator\Setting;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Migrator\FileMigration;

use function array_key_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;

use function version_compare;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final class AddOpenAiKeyToSettings implements FileMigration
{
    public function __construct(
        private readonly string $openAIKey,
    ) {
    }

    public function isSupporting(FileType $type, string $fileVersion): bool
    {
        return $type === FileType::SETTINGS && version_compare($fileVersion, '0.5', '<');
    }

    public function migrate(string $file, FileType $type): void
    {
        $fileContent = file_get_contents($file);
        if ($fileContent === false) {
            return;
        }

        $jsonArr = json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);

        if (array_key_exists('application', $jsonArr)) {
            // Nothing to do :)
            return;
        }

        $jsonArr['application']['open_ai_api_key'] = $this->openAIKey;

        file_put_contents($file, json_encode($jsonArr, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
