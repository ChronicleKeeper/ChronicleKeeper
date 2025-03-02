<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\ValueObject;

use ChronicleKeeper\Calendar\Domain\ValueObject\WeekDay;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(WeekDay::class)]
#[Small]
final class WeekDayTest extends TestCase
{
    #[Test]
    public function itIsCreatableFromArray(): void
    {
        $weekDay = WeekDay::fromArray([
            'index' => 3,
            'name' => 'Wednesday',
        ]);

        self::assertSame(3, $weekDay->index);
        self::assertSame('Wednesday', $weekDay->name);
    }
}
