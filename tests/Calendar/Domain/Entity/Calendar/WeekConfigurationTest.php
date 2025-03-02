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
    public function itCalculatesFirstWeekDaysCorrectWithRegularDayCalendar(CalendarDate $date, string $expectedDate): void
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

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'First day of the linear calendar is also the first weekday' => [
            new CalendarDate($calendar, 0, 1, 1),
            '1. Taranis 0 after the Flood',
        ];

        yield 'First day of the tenth month in the year must not be a leap day' => [
            new CalendarDate($calendar, 0, 10, 1),
            '1. Cerun 0 after the Flood',
        ];

        $calendar = ExampleCalendars::getCalendarWithLeapDayAsFirstDayOfTheYear();

        yield 'Leap day calendar start is able to deliver the every first weekday' => [
            new CalendarDate($calendar, 0, 1, 1),
            '1. First 0 AD',
        ];
    }

    #[Test]
    #[DataProvider('provideLastWeekDayCases')]
    public function itCalculatesLastWeekDaysCorrectWithRegularDayCalendar(CalendarDate $date, string $expectedDate): void
    {
        $calendarWeeks = $date->getCalendar()->getWeeks();

        self::assertSame($expectedDate, $calendarWeeks->getLastDayOfWeekByDate($date)->format());
    }

    public static function provideLastWeekDayCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'First day of the regular calendar is also not the first weekday' => [
            new CalendarDate($calendar, 0, 1, 1),
            '7. January 0 AD',
        ];

        yield 'Last day of the regular calendar first week is also the last week day' => [
            new CalendarDate($calendar, 0, 1, 7),
            '7. January 0 AD',
        ];

        yield 'Some day in the first week of the regular calendar will move to last day' => [
            new CalendarDate($calendar, 0, 1, 2),
            '7. January 0 AD',
        ];

        yield 'Some random day in the regular calendar year 0 has correct last weekday' => [
            new CalendarDate($calendar, 0, 7, 15),
            '15. July 0 AD',
        ];

        yield 'A random day in the regular calendar year 15 has correct last weekday' => [
            new CalendarDate($calendar, 12, 7, 15),
            '17. July 12 AD',
        ];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Last day of the linear calendar is not the first weekday' => [
            new CalendarDate($calendar, 0, 1, 1),
            '10. Taranis 0 after the Flood',
        ];

        yield 'Last last weekday of the linear calendar is the correct last week, even with leap day in week' => [
            new CalendarDate($calendar, 0, 1, 10),
            '10. Taranis 0 after the Flood',
        ];

        yield 'Last day of the tenth month in the year must not be a leap day' => [
            new CalendarDate($calendar, 0, 10, 1),
            '10. Cerun 0 after the Flood',
        ];
    }

    #[Test]
    public function testFromArray(): void
    {
        $weekDays = [
            ['index' => 1, 'name' => 'Monday'],
            ['index' => 2, 'name' => 'Tuesday'],
        ];

        $config = WeekConfiguration::fromArray($weekDays);
        $days   = $config->getDays();

        self::assertCount(2, $days);

        self::assertEquals('Monday', $days[1]->name);
        self::assertEquals(1, $days[1]->index);
        self::assertEquals('Tuesday', $days[2]->name);
        self::assertEquals(2, $days[2]->index);
    }
}
