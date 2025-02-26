<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\ValueObject\Settings\CalendarSettings;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\LeapDaySettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(LeapDaySettings::class)]
#[Small]
final class LeapDaySettingsTest extends TestCase
{
    #[Test]
    public function itConstructsFromArray(): void
    {
        $data = [
            'day' => 29,
            'name' => 'Leap Day',
            'year_interval' => 4,
        ];

        $settings = LeapDaySettings::fromArray($data);

        self::assertSame(29, $settings->getDay());
        self::assertSame('Leap Day', $settings->getName());
        self::assertSame(4, $settings->getYearInterval());
    }

    #[Test]
    public function itConstructsFromArrayWithoutYearInterval(): void
    {
        $data = [
            'day' => 29,
            'name' => 'Leap Day',
        ];

        $settings = LeapDaySettings::fromArray($data);

        self::assertSame(29, $settings->getDay());
        self::assertSame('Leap Day', $settings->getName());
        self::assertNull($settings->getYearInterval());
    }

    #[Test]
    public function itConvertsToArray(): void
    {
        $settings = new LeapDaySettings(29, 'Leap Day', 4);

        $expected = [
            'day' => 29,
            'name' => 'Leap Day',
            'year_interval' => 4,
        ];

        self::assertSame($expected, $settings->toArray());
    }

    #[Test]
    public function itConvertsToArrayWithoutYearInterval(): void
    {
        $settings = new LeapDaySettings(29, 'Leap Day');

        $expected = [
            'day' => 29,
            'name' => 'Leap Day',
        ];

        self::assertSame($expected, $settings->toArray());
    }

    #[Test]
    public function itSerializesToJson(): void
    {
        $settings = new LeapDaySettings(29, 'Leap Day', 4);

        $expected = [
            'day' => 29,
            'name' => 'Leap Day',
            'year_interval' => 4,
        ];

        self::assertSame(
            json_encode($expected, JSON_THROW_ON_ERROR),
            json_encode($settings, JSON_THROW_ON_ERROR),
        );
    }
}
