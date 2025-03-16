<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\ValueObject\Settings\CalendarSettings;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\LeapDaySettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(MonthSettings::class)]
#[CoversClass(LeapDaySettings::class)]
#[Small]
final class MonthSettingsTest extends TestCase
{
    #[Test]
    public function itConstructsFromArray(): void
    {
        $data = [
            'index' => 1,
            'name' => 'January',
            'days' => 31,
            'leap_days' => [
                [
                    'day' => 29,
                    'name' => 'Leap Day',
                    'year_interval' => 4,
                ],
            ],
        ];

        $settings = MonthSettings::fromArray($data);

        self::assertSame(1, $settings->getIndex());
        self::assertSame('January', $settings->getName());
        self::assertSame(31, $settings->getDays());
        self::assertCount(1, $settings->getLeapDays());
    }

    #[Test]
    public function itConvertsToArray(): void
    {
        $settings = new MonthSettings(1, 'January', 31);

        $expected = [
            'index' => 1,
            'name' => 'January',
            'days' => 31,
        ];

        self::assertSame($expected, $settings->toArray());
    }

    #[Test]
    public function itSerializesToJson(): void
    {
        $settings = new MonthSettings(1, 'January', 31);

        $expected = [
            'index' => 1,
            'name' => 'January',
            'days' => 31,
        ];

        self::assertSame(
            json_encode($expected, JSON_THROW_ON_ERROR),
            json_encode($settings, JSON_THROW_ON_ERROR),
        );
    }
}
