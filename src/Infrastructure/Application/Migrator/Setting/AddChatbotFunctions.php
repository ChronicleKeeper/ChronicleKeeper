<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Migrator\Setting;

use DZunke\NovDoc\Infrastructure\Application\FileType;
use DZunke\NovDoc\Infrastructure\Application\Migrator\FileMigration;

use function array_key_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function json_decode;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final class AddChatbotFunctions implements FileMigration
{
    public function isSupporting(FileType $type, string $fileVersion): bool
    {
        return $type === FileType::SETTINGS && $fileVersion === '0.2';
    }

    public function migrate(string $file): void
    {
        $fileContent = file_get_contents($file);
        if ($fileContent === false) {
            return;
        }

        $jsonArr = json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);

        if (
            ! is_array($jsonArr)
            || ! array_key_exists('chatbot', $jsonArr)
            || array_key_exists('functions', $jsonArr['chatbot'])
        ) {
            // Nothing to do :)
            return;
        }

        $jsonArr['chatbot']['functions']['allow_debug_output'] = false;

        file_put_contents($file, json_encode($jsonArr, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
