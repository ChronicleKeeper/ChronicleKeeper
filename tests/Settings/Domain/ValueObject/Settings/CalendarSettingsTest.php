<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\ValueObject\Settings;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\LeapDaySettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\WeekSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(CalendarSettings::class)]
#[CoversClass(EpochSettings::class)]
#[CoversClass(LeapDaySettings::class)]
#[CoversClass(MonthSettings::class)]
#[CoversClass(WeekSettings::class)]
#[Small]
final class CalendarSettingsTest extends TestCase
{
    #[Test]
    public function itConstructsFromArray(): void
    {
        $data = [
            'moon_cycle_days' => 301,
            'is_finished' => true,
            'months' => [
                [
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
                ],
            ],
            'epochs' => [
                [
                    'name' => 'First Age',
                    'start_year' => 1,
                    'end_year' => 100,
                ],
            ],
            'weeks' => [
                [
                    'index' => 1,
                    'name' => 'Monday',
                ],
            ],
        ];

        $settings = CalendarSettings::fromArray($data);

        self::assertCount(1, $settings->getMonths());
        self::assertCount(1, $settings->getEpochs());
        self::assertCount(1, $settings->getWeeks());

        self::assertSame(301.0, $settings->getMoonCycleDays());
        self::assertTrue($settings->isFinished());
    }

    #[Test]
    public function itConvertsToArray(): void
    {
        $leapDay = new LeapDaySettings(29, 'Leap Day', 4);
        $month   = new MonthSettings(1, 'January', 31, [$leapDay]);
        $epoch   = new EpochSettings('First Age', 1, 100);
        $week    = new WeekSettings(1, 'Monday');

        $settings = new CalendarSettings(301.0, true, [$month], [$epoch], [$week]);

        $expected = [
            'moon_cycle_days' => 301.0,
            'is_finished' => true,
            'months' => [
                [
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
                ],
            ],
            'epochs' => [
                [
                    'name' => 'First Age',
                    'start_year' => 1,
                    'end_year' => 100,
                ],
            ],
            'weeks' => [
                [
                    'index' => 1,
                    'name' => 'Monday',
                ],
            ],
        ];

        self::assertSame($expected, $settings->toArray());
    }

    #[Test]
    public function itSerializesToJson(): void
    {
        $leapDay = new LeapDaySettings(29, 'Leap Day', 4);
        $month   = new MonthSettings(1, 'January', 31, [$leapDay]);
        $epoch   = new EpochSettings('First Age', 1, 100);
        $week    = new WeekSettings(1, 'Monday');

        $settings = new CalendarSettings(35, false, [$month], [$epoch], [$week]);

        $expected = [
            'moon_cycle_days' => 35,
            'is_finished' => false,
            'months' => [
                [
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
                ],
            ],
            'epochs' => [
                [
                    'name' => 'First Age',
                    'start_year' => 1,
                    'end_year' => 100,
                ],
            ],
            'weeks' => [
                [
                    'index' => 1,
                    'name' => 'Monday',
                ],
            ],
        ];

        self::assertSame(
            json_encode($expected, JSON_THROW_ON_ERROR),
            json_encode($settings, JSON_THROW_ON_ERROR),
        );
    }
}
