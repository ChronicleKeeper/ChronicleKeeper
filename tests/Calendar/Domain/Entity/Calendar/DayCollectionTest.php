<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\DayCollection;
use ChronicleKeeper\Calendar\Domain\Entity\Calendar\RegularDay;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DayCollection::class)]
#[Small]
class DayCollectionTest extends TestCase
{
    #[Test]
    public function itWillCalculateTheDaysInTheMonthCorrectly(): void
    {
        $calendar = ExampleCalendars::getFullFeatured();
        $month    = $calendar->getMonthOfTheYear(1);

        self::assertCount(10, $month->days);

        $secondDayShouldBeTheFirstRegularDay = $month->days->getDay(2);
        self::assertInstanceOf(RegularDay::class, $secondDayShouldBeTheFirstRegularDay);
        self::assertSame('2', $secondDayShouldBeTheFirstRegularDay->getLabel());
    }
}
