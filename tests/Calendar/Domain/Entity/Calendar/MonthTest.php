<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\LeapDay;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Calendar\Domain\Exception\InvalidLeapDays;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\FullExampleCalendar;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Month::class)]
#[CoversClass(InvalidLeapDays::class)]
#[Small]
class MonthTest extends TestCase
{
    #[Test]
    public function itCanCountItsDaysWithoutLeapDays(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(1);

        self::assertSame(10, $month->getDayCount());
    }

    #[Test]
    public function itCanCountItsDaysWithLeapDays(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        self::assertSame(12, $month->getDayCount());
    }

    #[Test]
    public function itCanCountItsLeapDays(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        self::assertSame(2, $month->getLeapDaysCount());
    }

    #[Test]
    public function itFailsSettingLeapDaysWhenTheyAreAlreadyInitialized(): void
    {
        $this->expectException(InvalidLeapDays::class);
        $this->expectExceptionMessage('Leap days are already set for month 3');

        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        $month->setLeapDays(
            new LeapDay(11, 'EndOfYearFirstLeapDay'),
            new LeapDay(12, 'EndOfYearSecondLeapDay'),
        );
    }

    #[Test]
    public function itFailsSettingLeapDaysWhenTheyAreNotUnique(): void
    {
        $this->expectException(InvalidLeapDays::class);
        $this->expectExceptionMessage('Leap days are not unique by their index in month');

        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(1);

        $month->setLeapDays(
            new LeapDay(11, 'EndOfYearFirstLeapDay'),
            new LeapDay(11, 'EndOfYearFirstLeapDay'),
        );
    }

    #[Test]
    public function itFailsSettingLeapDaysWhenTheyAreStartingSequenceWithinRegularMonth(): void
    {
        $this->expectException(InvalidLeapDays::class);
        $this->expectExceptionMessage('Leap days are not a sequence from the max days of the month');

        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(1);

        $month->setLeapDays(
            new LeapDay(9, 'EndOfYearFirstLeapDay'),
            new LeapDay(10, 'EndOfYearFirstLeapDay'),
        );
    }

    #[Test]
    public function itFailsSettingLeapDaysWhenTheyAreNotSequenceFromLastDayOfMonthOn(): void
    {
        $this->expectException(InvalidLeapDays::class);
        $this->expectExceptionMessage('Leap days are not a sequence from the max days of the month');

        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(1);

        $month->setLeapDays(
            new LeapDay(11, 'EndOfYearFirstLeapDay'),
            new LeapDay(13, 'EndOfYearFirstLeapDay'),
        );
    }

    #[Test]
    public function itCanCheckADayIsALeapDay(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        self::assertTrue($month->isLeapDay(11));
    }

    #[Test]
    public function itCanCheckADayIsNotALeapDay(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        self::assertFalse($month->isLeapDay(10));
    }

    #[Test]
    public function itCanGetALeapDay(): void
    {
        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        self::assertSame('EndOfYearFirstLeapDay', $month->getLeapDay(11)->name);
    }

    #[Test]
    public function itFailsGettingANonExistentLeapDay(): void
    {
        $this->expectException(InvalidLeapDays::class);
        $this->expectExceptionMessage('The leap day 10 does not exist in month 3');

        $calendar = FullExampleCalendar::get();
        $month    = $calendar->getMonthOfTheYear(3);

        $month->getLeapDay(10);
    }
}
