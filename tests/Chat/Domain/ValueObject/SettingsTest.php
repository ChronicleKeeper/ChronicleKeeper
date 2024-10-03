<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\ValueObject;

use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Settings::class)]
#[Small]
class SettingsTest extends TestCase
{
    #[Test]
    public function constructsSettingsWithDefaultValues(): void
    {
        $settings = new Settings();

        self::assertSame(Version::gpt4oMini()->name, $settings->version);
        self::assertSame(0.7, $settings->temperature);
        self::assertSame(0.7, $settings->imagesMaxDistance);
        self::assertSame(0.85, $settings->documentsMaxDistance);
    }

    #[Test]
    public function constructsSettingsWithProvidedValues(): void
    {
        $settings = new Settings('customVersion', 0.9, 0.8, 0.9);

        self::assertSame('customVersion', $settings->version);
        self::assertSame(0.9, $settings->temperature);
        self::assertSame(0.8, $settings->imagesMaxDistance);
        self::assertSame(0.9, $settings->documentsMaxDistance);
    }
}
