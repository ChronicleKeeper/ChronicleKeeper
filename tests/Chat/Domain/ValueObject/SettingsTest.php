<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\ValueObject;

use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const PHP_FLOAT_EPSILON;

#[CoversClass(Settings::class)]
#[Small]
class SettingsTest extends TestCase
{
    #[Test]
    public function constructsSettingsWithDefaultValues(): void
    {
        $settings = new Settings();

        self::assertSame('gpt-4o-mini', $settings->version);
        self::assertEqualsWithDelta(0.7, $settings->temperature, PHP_FLOAT_EPSILON);
        self::assertEqualsWithDelta(0.7, $settings->imagesMaxDistance, PHP_FLOAT_EPSILON);
        self::assertEqualsWithDelta(0.85, $settings->documentsMaxDistance, PHP_FLOAT_EPSILON);
    }

    #[Test]
    public function constructsSettingsWithProvidedValues(): void
    {
        $settings = new Settings('customVersion', 0.9, 0.8, 0.9);

        self::assertSame('customVersion', $settings->version);
        self::assertEqualsWithDelta(0.9, $settings->temperature, PHP_FLOAT_EPSILON);
        self::assertEqualsWithDelta(0.8, $settings->imagesMaxDistance, PHP_FLOAT_EPSILON);
        self::assertEqualsWithDelta(0.9, $settings->documentsMaxDistance, PHP_FLOAT_EPSILON);
    }

    #[Test]
    public function itCanCompareTheEqualityOfsettings(): void
    {
        $settings1 = new Settings(temperature: 0.7, imagesMaxDistance: 0.7, documentsMaxDistance: 0.85);
        $settings2 = new Settings(temperature: 0.2, imagesMaxDistance: 0.7, documentsMaxDistance: 0.85);
        $settings3 = new Settings(temperature: 0.7, imagesMaxDistance: 0.7, documentsMaxDistance: 0.3);

        self::assertTrue($settings1->equals($settings1));
        self::assertFalse($settings1->equals($settings2));
        self::assertFalse($settings1->equals($settings3));
        self::assertFalse($settings2->equals($settings3));
    }
}
