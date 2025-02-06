<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\ValueObject\RegularDay;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DayCollection::class)]
#[CoversClass(RegularDay::class)]
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
            $calendar->getMonthOfTheYear(1)->days,
            1,
            '1',
        ];

        yield 'Get another regular day in a regular month' => [
            $calendar->getMonthOfTheYear(4)->days,
            5,
            '5',
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
    ): void {
        self::assertCount($expectedGeneralCount, $dayCollection);
        self::assertSame($expectedCountInYear, $dayCollection->countInYear($inYear));
        self::assertSame($expectedCountOfRegularDays, $dayCollection->getRegularDaysCount());
    }

    public static function provideDayCountCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Get a regular day in a regular month' => [
            $calendar->getMonthOfTheYear(1)->days,
            1,
            31,
            31,
            31,
        ];

        yield 'Get another regular day in a regular month' => [
            $calendar->getMonthOfTheYear(4)->days,
            5,
            30,
            30,
            30,
        ];
    }
}
