<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\LeapDay;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\RegularDay;
use ChronicleKeeper\Calendar\Domain\Exception\InvalidLeapDays;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\FullExampleCalendar;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DayCollection::class)]
#[CoversClass(InvalidLeapDays::class)]
#[Small]
class DayCollectionTest extends TestCase
{
    #[Test]
    public function itCanCountItsDaysWithoutLeapDays(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(1);

        self::assertCount(11, $month->days);
    }

    #[Test]
    public function itCanCountItsDaysWithLeapDays(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        self::assertCount(12, $month->days);
    }

    #[Test]
    public function itCanCountItsLeapDays(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        self::assertSame(2, $month->days->getLeapDaysCount());
    }

    #[Test]
    public function itFailsSettingLeapDaysWhenTheyAreNotUnique(): void
    {
        $this->expectException(InvalidLeapDays::class);
        $this->expectExceptionMessage('Leap days are not unique by their index in month');

        new DayCollection(
            10,
            new LeapDay(11, 'EndOfYearFirstLeapDay'),
            new LeapDay(11, 'EndOfYearFirstLeapDay'),
        );
    }

    #[Test]
    public function itCanCheckADayIsALeapDay(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        self::assertTrue($month->days->isLeapDay(11));
    }

    #[Test]
    public function itCanCheckADayIsNotALeapDay(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        self::assertFalse($month->days->isLeapDay(10));
    }

    #[Test]
    public function itWillCalculateTheDaysInTheMonthCorrectly(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(1);

        self::assertCount(11, $month->days);

        $firstDayShouldBeLeapDay = $month->days->getDay(1);
        self::assertInstanceOf(LeapDay::class, $firstDayShouldBeLeapDay);
        self::assertSame('NewYearsLeapDay', $firstDayShouldBeLeapDay->getLabel());

        $secondDayShouldBeTheFirstRegularDay = $month->days->getDay(2);
        self::assertInstanceOf(RegularDay::class, $secondDayShouldBeTheFirstRegularDay);
        self::assertSame('1', $secondDayShouldBeTheFirstRegularDay->getLabel());
    }
}
