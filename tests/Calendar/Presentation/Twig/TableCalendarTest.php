<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Presentation\Twig;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\WeekConfiguration;
use ChronicleKeeper\Calendar\Domain\Entity\CalendarDate;
use ChronicleKeeper\Calendar\Presentation\Twig\TableCalendar;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

use function array_map;
use function assert;
use function count;
use function iterator_to_array;
use function sprintf;

#[CoversClass(TableCalendar::class)]
#[CoversClass(Calendar::class)]
#[CoversClass(CalendarDate::class)]
#[CoversClass(WeekConfiguration::class)]
#[Medium]
final class TableCalendarTest extends KernelTestCase
{
    use InteractsWithTwigComponents;

    /** @param list<list<int>> $expectedWeeks */
    #[Test]
    #[DataProvider('provideCalendarWeeksCalculationCases')]
    public function itCanCalculateACorrectCalendarWeeks(
        CalendarDate $date,
        array $expectedWeeks,
    ): void {
        $component = $this->mountTwigComponent(
            name: TableCalendar::class,
            data: ['currentDate' => $date, 'viewDate' => $date, 'calendar' => $date->getCalendar()],
        );

        assert($component instanceof TableCalendar);

        $weeks = iterator_to_array($component->createCalendarOfMonth($date));
        self::assertCount(count($expectedWeeks), $weeks, 'The amount of weeks differs.');

        foreach ($weeks as $index => $week) {
            self::assertSame(
                $expectedWeeks[$index],
                array_map(static fn (CalendarDate $date) => (int) $date->getDay()->getLabel(), $week),
                sprintf('Week %d does not match the expected week.', $index + 1),
            );
        }
    }

    public static function provideCalendarWeeksCalculationCases(): Generator
    {
        $calendar = ExampleCalendars::getOnlyRegularDays();

        yield 'Regular Calendar - Year 0, Month 1' => [
            new CalendarDate($calendar, 0, 1, 1),
            [
                [1, 2, 3, 4, 5, 6, 7],
                [8, 9, 10, 11, 12, 13, 14],
                [15, 16, 17, 18, 19, 20, 21],
                [22, 23, 24, 25, 26, 27, 28],
                [29, 30, 31, 1, 2, 3, 4],
            ],
        ];

        yield 'Regular Calendar - Year 0, Month 2' => [
            new CalendarDate($calendar, 0, 2, 1),
            [
                [29, 30, 31, 1, 2, 3, 4],
                [5, 6, 7, 8, 9, 10, 11],
                [12, 13, 14, 15, 16, 17, 18],
                [19, 20, 21, 22, 23, 24, 25],
                [26, 27, 28, 1, 2, 3, 4],
            ],
        ];

        yield 'Regular Calendar - Year 0, Month 3' => [
            new CalendarDate($calendar, 0, 3, 1),
            [
                [26, 27, 28, 1, 2, 3, 4],
                [5, 6, 7, 8, 9, 10, 11],
                [12, 13, 14, 15, 16, 17, 18],
                [19, 20, 21, 22, 23, 24, 25],
                [26, 27, 28, 29, 30, 31, 1],
            ],
        ];

        yield 'Regular Calendar - Year 0, Month 4' => [
            new CalendarDate($calendar, 0, 4, 1),
            [
                [26, 27, 28, 29, 30, 31, 1],
                [2, 3, 4, 5, 6, 7, 8],
                [9, 10, 11, 12, 13, 14, 15],
                [16, 17, 18, 19, 20, 21, 22],
                [23, 24, 25, 26, 27, 28, 29],
                [30, 1, 2, 3, 4, 5, 6],
            ],
        ];

        yield 'Regular Calendar - Year 0, Month 5' => [
            new CalendarDate($calendar, 0, 5, 1),
            [
                [30, 1, 2, 3, 4, 5, 6],
                [7, 8, 9, 10, 11, 12, 13],
                [14, 15, 16, 17, 18, 19, 20],
                [21, 22, 23, 24, 25, 26, 27],
                [28, 29, 30, 31, 1, 2, 3],
            ],
        ];

        $calendar = ExampleCalendars::getLinearWithLeapDays();

        yield 'Linear Calendar - Year 0, Month 1' => [
            new CalendarDate($calendar, 0, 1, 1),
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
                [21, 22, 23, 24, 25, 26, 27, 28, 29, 30],
            ],
        ];

        yield 'Linear Calendar - Year 0, Month 2' => [
            new CalendarDate($calendar, 0, 2, 1),
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
                [21, 22, 23, 24, 25, 26, 27, 28, 29, 30],
            ],
        ];

        yield 'Linear Calendar - Year 0, Month 10' => [
            new CalendarDate($calendar, 0, 10, 1),
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
                [21, 22, 23, 24, 25, 26, 27, 28, 29, 30],
            ],
        ];

        yield 'Linear Calendar - Year 5, Month 10' => [
            new CalendarDate($calendar, 5, 10, 1),
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
                [21, 22, 23, 24, 25, 26, 27, 28, 29, 30],
            ],
        ];
    }
}
