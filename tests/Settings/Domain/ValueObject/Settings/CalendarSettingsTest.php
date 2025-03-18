<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\ValueObject\Settings;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
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
#[CoversClass(CurrentDay::class)]
#[Small]
final class CalendarSettingsTest extends TestCase
{
    #[Test]
    public function itConstructsFromArray(): void
    {
        $data = [
            'moons' => [
                [
                    'moon_name' => 'Moon',
                    'moon_cycle_days' => 35,
                    'moon_cycle_offset' => 10.2,
                ],
            ],
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
            'current_day' => [
                'year' => 1262,
                'month' => 1,
                'day' => 21,
            ],
        ];

        $settings = CalendarSettings::fromArray($data);

        self::assertCount(1, $settings->getMonths());
        self::assertCount(1, $settings->getEpochs());
        self::assertCount(1, $settings->getWeeks());

        self::assertSame('Moon', $settings->getMoonName());
        self::assertSame(35.0, $settings->getMoonCycleDays());
        self::assertSame(10.2, $settings->getMoonCycleOffset());
        self::assertTrue($settings->isFinished());

        $currentDay = $settings->getCurrentDay();
        self::assertNotNull($currentDay);
        self::assertSame(1262, $currentDay->getYear());
        self::assertSame(1, $currentDay->getMonth());
        self::assertSame(21, $currentDay->getDay());
    }

    #[Test]
    public function itConstructsFromArrayWithoutCurrentDay(): void
    {
        $data = [
            'moons' => [
                [
                    'moon_name' => 'Moon',
                    'moon_cycle_days' => 35,
                    'moon_cycle_offset' => 10.2,
                ],
            ],
            'is_finished' => true,
            'months' => [
                [
                    'index' => 1,
                    'name' => 'January',
                    'days' => 31,
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
        self::assertNull($settings->getCurrentDay());
    }

    #[Test]
    public function itConstructsFromArrayWithoutMoons(): void
    {
        $data = [
            'moons' => [],
            'is_finished' => true,
            'months' => [
                [
                    'index' => 1,
                    'name' => 'January',
                    'days' => 31,
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
            'current_day' => [
                'year' => 1262,
                'month' => 1,
                'day' => 21,
            ],
        ];

        $settings = CalendarSettings::fromArray($data);

        self::assertSame('Mond', $settings->getMoonName());
        self::assertSame(30.0, $settings->getMoonCycleDays());
        self::assertSame(0.0, $settings->getMoonCycleOffset());
    }

    #[Test]
    public function itConvertsToArray(): void
    {
        $leapDay    = new LeapDaySettings(29, 'Leap Day', 4);
        $month      = new MonthSettings(1, 'January', 31, [$leapDay]);
        $epoch      = new EpochSettings('First Age', 1, 100);
        $week       = new WeekSettings(1, 'Monday');
        $currentDay = new CurrentDay(1262, 1, 21);

        $settings = new CalendarSettings(
            'Moon',
            301.0,
            10.2,
            true,
            [$month],
            [$epoch],
            [$week],
            $currentDay,
        );

        $expected = [
            'moons' => [
                [
                    'moon_name' => 'Moon',
                    'moon_cycle_days' => 301.0,
                    'moon_cycle_offset' => 10.2,
                ],
            ],
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
            'current_day' => [
                'year' => 1262,
                'month' => 1,
                'day' => 21,
            ],
        ];

        self::assertSame($expected, $settings->toArray());
    }

    #[Test]
    public function itConvertsToArrayWithoutCurrentDay(): void
    {
        $leapDay = new LeapDaySettings(29, 'Leap Day', 4);
        $month   = new MonthSettings(1, 'January', 31, [$leapDay]);
        $epoch   = new EpochSettings('First Age', 1, 100);
        $week    = new WeekSettings(1, 'Monday');

        $settings = new CalendarSettings(
            'Moon',
            301.0,
            10.2,
            true,
            [$month],
            [$epoch],
            [$week],
        );

        $expected = [
            'moons' => [
                [
                    'moon_name' => 'Moon',
                    'moon_cycle_days' => 301.0,
                    'moon_cycle_offset' => 10.2,
                ],
            ],
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
            'current_day' => null,
        ];

        self::assertSame($expected, $settings->toArray());
    }

    #[Test]
    public function itSerializesToJson(): void
    {
        $leapDay    = new LeapDaySettings(29, 'Leap Day', 4);
        $month      = new MonthSettings(1, 'January', 31, [$leapDay]);
        $epoch      = new EpochSettings('First Age', 1, 100);
        $week       = new WeekSettings(1, 'Monday');
        $currentDay = new CurrentDay(1262, 1, 21);

        $settings = new CalendarSettings(
            'Moon',
            35,
            10.2,
            false,
            [$month],
            [$epoch],
            [$week],
            $currentDay,
        );

        $expected = [
            'moons' => [
                [
                    'moon_name' => 'Moon',
                    'moon_cycle_days' => 35,
                    'moon_cycle_offset' => 10.2,
                ],
            ],
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
            'current_day' => [
                'year' => 1262,
                'month' => 1,
                'day' => 21,
            ],
        ];

        self::assertSame(
            json_encode($expected, JSON_THROW_ON_ERROR),
            json_encode($settings, JSON_THROW_ON_ERROR),
        );
    }
}
