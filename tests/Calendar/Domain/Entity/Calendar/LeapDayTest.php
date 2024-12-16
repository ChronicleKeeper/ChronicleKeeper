<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\LeapDay;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LeapDay::class)]
#[Small]
class LeapDayTest extends TestCase
{
    #[Test]
    public function itHasADayOfTheMonth(): void
    {
        $leapDay = new LeapDay(29, 'LeapDay');

        self::assertSame(29, $leapDay->dayOfTheMonth);
    }

    #[Test]
    public function itHasAName(): void
    {
        $leapDay = new LeapDay(29, 'LeapDay');

        self::assertSame('LeapDay', $leapDay->name);
    }
}
