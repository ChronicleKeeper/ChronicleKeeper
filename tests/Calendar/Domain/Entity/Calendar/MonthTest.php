<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Month;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Month::class)]
#[Small]
final class MonthTest extends TestCase
{
    #[Test]
    #[DataProvider('provideCountDaysInYearCases')]
    public function itIsCalculationTheDaysInAYearCorrect(
        Month $month,
        int $year,
        int $expectedDayCount,
    ): void {
        self::assertCount($expectedDayCount, $month->getDaysInYear($year));
    }

    public static function provideCountDaysInYearCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'January 0 in regular days calendar' => [
            'month' => $calendar->getMonth(1),
            'year' => 0,
            'expectedDayCount' => 31,
        ];

        yield 'February 0 in regular days calendar' => [
            'month' => $calendar->getMonth(2),
            'year' => 0,
            'expectedDayCount' => 28,
        ];

        yield 'July 234 in regular days calendar' => [
            'month' => $calendar->getMonth(7),
            'year' => 234,
            'expectedDayCount' => 31,
        ];
    }
}
