<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Exception\DayNotExistsInMonth;
use ChronicleKeeper\Calendar\Domain\ValueObject\LeapDay;
use ChronicleKeeper\Calendar\Domain\ValueObject\RegularDay;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function sprintf;

#[CoversClass(DayCollection::class)]
#[CoversClass(RegularDay::class)]
#[CoversClass(LeapDay::class)]
#[CoversClass(DayNotExistsInMonth::class)]
#[Small]
class DayCollectionTest extends TestCase
{
    #[Test]
    public function itWillCalculateTheDaysOnCreationWithOnlyRegularDays(): void
    {
        $dayCollection = new DayCollection(31);

        self::assertCount(31, $dayCollection);

        $aRegularDayToTest = $dayCollection->getDay(12);
        self::assertInstanceOf(RegularDay::class, $aRegularDayToTest);
        self::assertSame(12, $aRegularDayToTest->getDayOfTheMonth());
        self::assertSame('12', $aRegularDayToTest->getLabel());
    }

    #[Test]
    public function itWillCalculateTheDaysOnCreationWithMixedDays(): void
    {
        $dayCollection = new DayCollection(
            31,
            new LeapDay(1, 'First Day'),
            new LeapDay(15, 'Second Day'),
        );

        self::assertCount(33, $dayCollection);

        $aRegularDayToTest = $dayCollection->getDay(12);
        self::assertInstanceOf(RegularDay::class, $aRegularDayToTest);
        self::assertSame(12, $aRegularDayToTest->getDayOfTheMonth());
        self::assertSame('11', $aRegularDayToTest->getLabel());

        $aRegularDayToTest = $dayCollection->getDay(33);
        self::assertInstanceOf(RegularDay::class, $aRegularDayToTest);
        self::assertSame(33, $aRegularDayToTest->getDayOfTheMonth());
        self::assertSame('31', $aRegularDayToTest->getLabel());

        $aLeapDayToTest = $dayCollection->getDay(1);
        self::assertInstanceOf(LeapDay::class, $aLeapDayToTest);
        self::assertSame(1, $aLeapDayToTest->getDayOfTheMonth());
        self::assertSame('First Day', $aLeapDayToTest->getLabel());

        $aLeapDayToTest = $dayCollection->getDay(15);
        self::assertInstanceOf(LeapDay::class, $aLeapDayToTest);
        self::assertSame(15, $aLeapDayToTest->getDayOfTheMonth());
        self::assertSame('Second Day', $aLeapDayToTest->getLabel());
    }

    #[Test]
    public function itCalculatesTheDayCollectionCorrectWithALeapDayAtTheEnd(): void
    {
        $dayCollection = new DayCollection(
            30,
            new LeapDay(31, 'Last Day'),
        );

        self::assertCount(31, $dayCollection);

        $aRegularDayToTest = $dayCollection->getDay(1);
        self::assertInstanceOf(RegularDay::class, $aRegularDayToTest);
        self::assertSame(1, $aRegularDayToTest->getDayOfTheMonth());
        self::assertSame('1', $aRegularDayToTest->getLabel());

        $aLeapDayToTest = $dayCollection->getDay(31);
        self::assertInstanceOf(LeapDay::class, $aLeapDayToTest);
        self::assertSame(31, $aLeapDayToTest->getDayOfTheMonth());
        self::assertSame('Last Day', $aLeapDayToTest->getLabel());
    }

    #[Test]
    public function itCanHaveMultipleLeapDaysAtTheEndOfAMonth(): void
    {
        $dayCollection = new DayCollection(
            30,
            new LeapDay(31, 'Last Day'),
            new LeapDay(32, 'Really Last Day'),
            new LeapDay(33, 'Really Really Last Day'),
            new LeapDay(37, 'Too Far Away Day'),
        );

        self::assertCount(33, $dayCollection);

        $aRegularDayToTest = $dayCollection->getDay(1);
        self::assertInstanceOf(RegularDay::class, $aRegularDayToTest);
        self::assertSame(1, $aRegularDayToTest->getDayOfTheMonth());
        self::assertSame('1', $aRegularDayToTest->getLabel());

        $aLeapDayToTest = $dayCollection->getDay(31);
        self::assertInstanceOf(LeapDay::class, $aLeapDayToTest);
        self::assertSame(31, $aLeapDayToTest->getDayOfTheMonth());
        self::assertSame('Last Day', $aLeapDayToTest->getLabel());

        $aLeapDayToTest = $dayCollection->getDay(32);
        self::assertInstanceOf(LeapDay::class, $aLeapDayToTest);
        self::assertSame(32, $aLeapDayToTest->getDayOfTheMonth());
        self::assertSame('Really Last Day', $aLeapDayToTest->getLabel());

        $aLeapDayToTest = $dayCollection->getDay(33);
        self::assertInstanceOf(LeapDay::class, $aLeapDayToTest);
        self::assertSame(33, $aLeapDayToTest->getDayOfTheMonth());
        self::assertSame('Really Really Last Day', $aLeapDayToTest->getLabel());
    }

    #[Test]
    public function itIsNotAbleTogetANonExistentDay(): void
    {
        $this->expectException(DayNotExistsInMonth::class);
        $this->expectExceptionMessage('Day 9 does not exist in the month, in any year');

        $dayCollection = new DayCollection(5);
        $dayCollection->getDay(9);
    }

    #[Test]
    public function itIgnoresLeapDaysThatAreTooFarAwayFromMontEnding(): void
    {
        $this->expectException(DayNotExistsInMonth::class);
        $this->expectExceptionMessage('Day 32 does not exist in the month, in any year');

        $dayCollection = new DayCollection(
            30,
            new LeapDay(32, 'Far Far Away Day'),
        );
        self::assertCount(30, $dayCollection);

        $dayCollection->getDay(32);
    }

    #[Test]
    public function itIsAbleToFetchASpecificDayOnADateIgnoringALeapDayFromInterval(): void
    {
        $dayCollection = new DayCollection(
            15,
            new LeapDay(3, 'First Day', 2),
        );

        self::assertInstanceOf(RegularDay::class, $dayCollection->getDayInYear(3, 1));
        self::assertInstanceOf(LeapDay::class, $dayCollection->getDayInYear(3, 2));
    }

    #[Test]
    public function itIsAbleToFetchASpecificDayWhenThereAreNoLeapDays(): void
    {
        $dayCollection = new DayCollection(15);

        self::assertInstanceOf(RegularDay::class, $dayCollection->getDayInYear(3, 1));
    }

    #[Test]
    #[DataProvider('provideDayCalculationCases')]
    public function itWillGetTheCorrectDayLabelForDayInMonth(
        DayCollection $dayCollection,
        int $day,
        string $expectedLabel,
    ): void {
        self::assertSame($expectedLabel, $dayCollection->getDay($day)->getLabel());
    }

    public static function provideDayCalculationCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Get a regular day in a regular month' => [
            $calendar->getMonth(1)->days,
            1,
            '1',
        ];

        yield 'Get another regular day in a regular month' => [
            $calendar->getMonth(4)->days,
            5,
            '5',
        ];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Get a leap day in a month with leap days' => [
            $calendar->getMonth(1)->days,
            3,
            'Mithwinter',
        ];

        yield 'Get a regular day in a month with leap days' => [
            $calendar->getMonth(1)->days,
            4,
            '3',
        ];

        yield 'Get another regular day in a month with leap days' => [
            $calendar->getMonth(7)->days,
            15,
            '14',
        ];
    }

    #[Test]
    #[DataProvider('provideDayCountCases')]
    public function itWillCountTheDaysCorrectly(
        DayCollection $dayCollection,
        int $inYear,
        int $expectedGeneralCount,
        int $expectedCountInYear,
        int $expectedCountOfRegularDays,
        int $expectedCountOfLeapDays,
    ): void {
        self::assertCount(
            $expectedGeneralCount,
            $dayCollection,
            'Total count, utilizing count interface, of days is incorrect',
        );
        self::assertSame(
            $expectedCountInYear,
            $dayCollection->countInYear($inYear),
            sprintf('Count of days in year %d is incorrect', $inYear),
        );
        self::assertSame(
            $expectedCountOfRegularDays,
            $dayCollection->countRegularDays(),
            'Count of regular days is incorrect',
        );
        self::assertSame(
            $expectedCountOfLeapDays,
            $dayCollection->countLeapDaysInYear($inYear),
            'Count of leap days is incorrect',
        );
    }

    public static function provideDayCountCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Get a regular day in a regular month' => [
            $calendar->getMonth(1)->days,
            1,
            31,
            31,
            31,
            0,
        ];

        yield 'Get another regular day in a regular month' => [
            $calendar->getMonth(4)->days,
            5,
            30,
            30,
            30,
            0,
        ];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Get a leap day in a month with leap days' => [
            $calendar->getMonth(1)->days,
            1,
            31,
            31,
            30,
            1,
        ];

        yield 'Get a leap day in a month with multiple leap days' => [
            $calendar->getMonth(7)->days,
            1,
            32,
            31,
            30,
            1,
        ];

        yield 'Get a leap day in a month with multiple leap days in day where leap day is ignored' => [
            $calendar->getMonth(7)->days,
            4,
            32,
            32,
            30,
            2,
        ];
    }

    #[Test]
    public function itCanCountTheLeapDaysUpToADateInAYear(): void
    {
        $dayCollection = new DayCollection(
            30,
            new LeapDay(15, 'Some Leap Day', 2),
        );

        self::assertSame(0, $dayCollection->countLeapDaysUpToDayInYear(14, 1));
        self::assertSame(1, $dayCollection->countLeapDaysUpToDayInYear(16, 2));
        self::assertSame(0, $dayCollection->countLeapDaysUpToDayInYear(16, 3));
    }

    #[Test]
    public function itCanGetTheLeapDaysInAYear(): void
    {
        $dayCollection = new DayCollection(
            30,
            new LeapDay(15, 'Some Leap Day', 2),
            new LeapDay(16, 'Some Leap Day'),
        );

        self::assertCount(2, $dayCollection->getLeapDaysInYear(2));
        self::assertCount(1, $dayCollection->getLeapDaysInYear(1));
    }

    #[Test]
    public function itCreatesCollectionWithOnlyRegularDays(): void
    {
        $data = ['regularDays' => 30];

        $collection = DayCollection::fromArray($data);

        self::assertCount(30, $collection);
        self::assertSame(30, $collection->countRegularDays());
        self::assertSame(0, $collection->countLeapDaysInYear(1));
        self::assertInstanceOf(RegularDay::class, $collection->getDay(1));
        self::assertSame('1', $collection->getDay(1)->getLabel());
        self::assertInstanceOf(RegularDay::class, $collection->getDay(30));
        self::assertSame('30', $collection->getDay(30)->getLabel());
    }

    #[Test]
    public function itCreatesCollectionWithLeapDays(): void
    {
        $data = [
            'regularDays' => 30,
            'leapDays' => [
                ['day' => 3, 'name' => 'Special Day'],
                ['day' => 15, 'name' => 'Mid Month', 'yearInterval' => 4],
            ],
        ];

        $collection = DayCollection::fromArray($data);

        self::assertCount(32, $collection);
        self::assertSame(30, $collection->countRegularDays());
        self::assertSame(2, $collection->countLeapDaysInYear(4));
        self::assertSame(1, $collection->countLeapDaysInYear(1));

        // Check leap days
        self::assertInstanceOf(LeapDay::class, $collection->getDay(3));
        self::assertSame('Special Day', $collection->getDay(3)->getLabel());

        self::assertInstanceOf(LeapDay::class, $collection->getDay(15));
        self::assertSame('Mid Month', $collection->getDay(15)->getLabel());

        // Check regular days have correct numbering
        self::assertInstanceOf(RegularDay::class, $collection->getDay(4));
        self::assertSame('3', $collection->getDay(4)->getLabel());

        self::assertInstanceOf(RegularDay::class, $collection->getDay(16));
        self::assertSame('14', $collection->getDay(16)->getLabel());
    }
}
