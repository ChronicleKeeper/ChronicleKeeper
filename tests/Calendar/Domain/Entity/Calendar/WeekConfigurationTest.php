<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekConfiguration;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Domain\Exception\InvalidWeekConfiguration;
use ChronicleKeeper\Calendar\Domain\ValueObject\WeekDay;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(WeekConfiguration::class)]
#[CoversClass(WeekDay::class)]
#[CoversClass(InvalidWeekConfiguration::class)]
#[Small]
final class WeekConfigurationTest extends TestCase
{
    #[Test]
    public function itFailsConstructionWithoutWeekDays(): void
    {
        $this->expectException(InvalidWeekConfiguration::class);
        $this->expectExceptionMessage('Week configuration must contain at least one day');

        new WeekConfiguration();
    }

    #[Test]
    public function itFailsConstructionWithNonSequentialWeekDays(): void
    {
        $this->expectException(InvalidWeekConfiguration::class);
        $this->expectExceptionMessage('Week days must be sequential');

        new WeekConfiguration(
            new WeekDay(1, 'Monday'),
            new WeekDay(3, 'Wednesday'),
            new WeekDay(4, 'Thursday'),
        );
    }

    #[Test]
    public function itReturnsWeekDays(): void
    {
        $weekConfiguration = new WeekConfiguration(
            $firstDay      = new WeekDay(1, 'Monday'),
            $secondDay     = new WeekDay(2, 'Tuesday'),
            $thirdDay      = new WeekDay(3, 'Wednesday'),
        );

        $weekDays = $weekConfiguration->getDays();

        self::assertSame(3, $weekConfiguration->countDays());
        self::assertCount(3, $weekDays);
        self::assertArrayHasKey(1, $weekDays);
        self::assertArrayHasKey(2, $weekDays);
        self::assertArrayHasKey(3, $weekDays);

        self::assertSame($firstDay, $weekDays[1]);
        self::assertSame($secondDay, $weekDays[2]);
        self::assertSame($thirdDay, $weekDays[3]);
    }

    #[Test]
    #[DataProvider('provideFirstWeekDayCases')]
    public function itCalculatesWeekDaysCorrectWithRegularDayCalendar(CalendarDate $date, string $expectedDate): void
    {
        $calendarWeeks = $date->getCalendar()->getWeeks();

        self::assertSame($expectedDate, $calendarWeeks->getFirstDayOfWeekByDate($date)->format());
    }

    public static function provideFirstWeekDayCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'First day of the regular calendar is also the first weekday' => [
            new CalendarDate($calendar, 0, 1, 1),
            '1. January 0 AD',
        ];

        yield 'Some day in the first week of the regular calendar will return to first day' => [
            new CalendarDate($calendar, 0, 1, 2),
            '1. January 0 AD',
        ];

        yield 'Some random day in the regular calendar year 0 has correct weekday' => [
            new CalendarDate($calendar, 0, 7, 15),
            '9. July 0 AD',
        ];

        yield 'A random day in the regular calendar year 15 has correct weekday' => [
            new CalendarDate($calendar, 12, 7, 15),
            '11. July 12 AD',
        ];
    }
}
