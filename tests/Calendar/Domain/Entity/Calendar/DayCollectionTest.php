<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\LeapDay;
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

        self::assertCount(10, $month->days);
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
    public function itFailsSettingLeapDaysWhenTheyAreStartingSequenceWithinRegularMonth(): void
    {
        $this->expectException(InvalidLeapDays::class);
        $this->expectExceptionMessage('Leap days are not a sequence from the max days of the month');

        new DayCollection(
            10,
            new LeapDay(9, 'EndOfYearFirstLeapDay'),
            new LeapDay(10, 'EndOfYearFirstLeapDay'),
        );
    }

    #[Test]
    public function itFailsSettingLeapDaysWhenTheyAreNotSequenceFromLastDayOfMonthOn(): void
    {
        $this->expectException(InvalidLeapDays::class);
        $this->expectExceptionMessage('Leap days are not a sequence from the max days of the month');

        new DayCollection(
            10,
            new LeapDay(11, 'EndOfYearFirstLeapDay'),
            new LeapDay(13, 'EndOfYearFirstLeapDay'),
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
    public function itCanGetALeapDay(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        self::assertSame('EndOfYearFirstLeapDay', $month->days->getLeapDay(11)->name);
    }

    #[Test]
    public function itFailsGettingANonExistentLeapDay(): void
    {
        $this->expectException(InvalidLeapDays::class);
        $this->expectExceptionMessage('The leap day 10 does not exist.');

        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        $month->days->getLeapDay(10);
    }
}
