<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\MoonCycle;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\ValueObject\MoonState;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MoonCycle::class)]
#[Small]
final class MoonCycleTest extends TestCase
{
    #[Test]
    #[DataProvider('provideMoonStateCasesWithNonLinearCalendar')]
    public function itCanCalculateTheCurrentMoonState(CalendarDate $date, MoonState $expectedMoonState): void
    {
        self::assertSame($expectedMoonState, $date->getMoonState());
    }

    public static function provideMoonStateCasesWithNonLinearCalendar(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Day 1: New Moon' => [
            new CalendarDate($calendar, 0, 1, 1),
            MoonState::NEW_MOON,
        ];

        yield 'Day 5: Waxing Crescent' => [
            new CalendarDate($calendar, 0, 1, 5),
            MoonState::WAXING_CRESCENT,
        ];

        yield 'Day 7: First Quarter' => [
            new CalendarDate($calendar, 0, 1, 7),
            MoonState::FIRST_QUARTER,
        ];

        yield 'Day 8: First Quarter' => [
            new CalendarDate($calendar, 0, 1, 8),
            MoonState::FIRST_QUARTER,
        ];

        yield 'Day 11: Waxing Gibbous' => [
            new CalendarDate($calendar, 0, 1, 11),
            MoonState::WAXING_GIBBOUS,
        ];

        yield 'Day 15: Full Moon' => [
            new CalendarDate($calendar, 0, 1, 15),
            MoonState::FULL_MOON,
        ];

        yield 'Day 18: Waning Gibbous' => [
            new CalendarDate($calendar, 0, 1, 18),
            MoonState::WANING_GIBBOUS,
        ];

        yield 'Day 22: Last Quarter' => [
            new CalendarDate($calendar, 0, 1, 22),
            MoonState::LAST_QUARTER,
        ];

        yield 'Day 26: Waning Crescent' => [
            new CalendarDate($calendar, 0, 1, 26),
            MoonState::WANING_CRESCENT,
        ];

        yield 'Day 29: New Moon' => [
            new CalendarDate($calendar, 0, 1, 29),
            MoonState::NEW_MOON,
        ];

        yield 'Day 34: Waxing Crescent' => [
            new CalendarDate($calendar, 0, 2, 4),
            MoonState::WAXING_CRESCENT,
        ];

        yield 'Day 36: First Quarter' => [
            new CalendarDate($calendar, 0, 2, 6),
            MoonState::FIRST_QUARTER,
        ];
    }
}
