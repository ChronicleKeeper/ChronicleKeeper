<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\ValueObject\Settings\CalendarSettings;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(WeekSettings::class)]
#[Small]
final class WeekSettingsTest extends TestCase
{
    #[Test]
    public function itConstructsFromArray(): void
    {
        $data = [
            'index' => 1,
            'name' => 'Monday',
        ];

        $settings = WeekSettings::fromArray($data);

        self::assertSame(1, $settings->getIndex());
        self::assertSame('Monday', $settings->getName());
    }

    #[Test]
    public function itConvertsToArray(): void
    {
        $settings = new WeekSettings(1, 'Monday');

        $expected = [
            'index' => 1,
            'name' => 'Monday',
        ];

        self::assertSame($expected, $settings->toArray());
    }

    #[Test]
    public function itSerializesToJson(): void
    {
        $settings = new WeekSettings(1, 'Monday');

        $expected = [
            'index' => 1,
            'name' => 'Monday',
        ];

        self::assertSame(
            json_encode($expected, JSON_THROW_ON_ERROR),
            json_encode($settings, JSON_THROW_ON_ERROR),
        );
    }
}
