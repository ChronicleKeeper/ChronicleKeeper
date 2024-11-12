<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Service\Migrator\Setting;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\Migrator\Setting\AddOpenAiKeyToSettings;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddOpenAiKeyToSettings::class)]
#[Small]
class AddOpenAiKeyToSettingsTest extends TestCase
{
    #[Test]
    #[DataProvider('provideSupportingTests')]
    public function testIsSupporting(FileType $type, string $fileVersion, bool $expectedResult): void
    {
        $fileAccess      = self::createStub(FileAccess::class);
        $settingsHandler = self::createStub(SettingsHandler::class);

        $addOpenAiKeyToSettings = new AddOpenAiKeyToSettings($fileAccess, $settingsHandler);
        self::assertSame($expectedResult, $addOpenAiKeyToSettings->isSupporting($type, $fileVersion));
    }

    public static function provideSupportingTests(): Generator
    {
        yield 'Supporting file type and version' => [FileType::DOTENV, '0.4', true];
        yield 'Not supporting version' => [FileType::DOTENV, '0.5', false];
        yield 'Not supporting file type' => [FileType::SETTINGS, '0.5', false];
    }

    #[Test]
    public function testMigrate(): void
    {
        $fileAccess = self::createStub(FileAccess::class);
        $fileAccess->method('read')->willReturn("APP_DEBUG=0\nOPENAI_API_KEY=foo\n");

        $settingsHandler = self::createStub(SettingsHandler::class);
        $settingsHandler->method('get')->willReturn($settings = new Settings());

        $addOpenAiKeyToSettings = new AddOpenAiKeyToSettings($fileAccess, $settingsHandler);
        $addOpenAiKeyToSettings->migrate('file', FileType::DOTENV);

        self::assertSame('foo', $settings->getApplication()->openAIApiKey);
    }
}
