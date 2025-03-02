<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\ValueObject;

use ChronicleKeeper\Calendar\Domain\ValueObject\LeapDay;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LeapDay::class)]
#[Small]
final class LeapDayTest extends TestCase
{
    #[Test]
    public function itIsCreatableFromArrayWithoutInterval(): void
    {
        $leapDay = LeapDay::fromArray([
            'day' => 29,
            'name' => 'Extra Day',
        ]);

        self::assertSame(29, $leapDay->getDayOfTheMonth());
        self::assertSame('Extra Day', $leapDay->getLabel());
        self::assertSame(1, $leapDay->yearInterval);
    }

    #[Test]
    public function itIsCreatableFromArrayWithInterval(): void
    {
        $leapDay = LeapDay::fromArray([
            'day' => 29,
            'name' => 'Leap Day',
            'yearInterval' => 4,
        ]);

        self::assertSame(29, $leapDay->getDayOfTheMonth());
        self::assertSame('Leap Day', $leapDay->getLabel());
        self::assertSame(4, $leapDay->yearInterval);
    }
}
