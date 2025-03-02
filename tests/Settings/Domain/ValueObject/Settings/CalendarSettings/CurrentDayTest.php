<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\ValueObject\Settings\CalendarSettings;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(CurrentDay::class)]
#[Small]
final class CurrentDayTest extends TestCase
{
    #[Test]
    public function itConstructsFromArray(): void
    {
        $data = [
            'year' => 2024,
            'month' => 6,
            'day' => 15,
        ];

        $currentDay = CurrentDay::fromArray($data);

        self::assertSame(2024, $currentDay->getYear());
        self::assertSame(6, $currentDay->getMonth());
        self::assertSame(15, $currentDay->getDay());
    }

    #[Test]
    public function itConvertsToArray(): void
    {
        $currentDay = new CurrentDay(2024, 6, 15);

        $expected = [
            'year' => 2024,
            'month' => 6,
            'day' => 15,
        ];

        self::assertSame($expected, $currentDay->toArray());
    }

    #[Test]
    public function itSerializesToJson(): void
    {
        $currentDay = new CurrentDay(2024, 6, 15);

        $expected = [
            'year' => 2024,
            'month' => 6,
            'day' => 15,
        ];

        self::assertSame(
            json_encode($expected, JSON_THROW_ON_ERROR),
            json_encode($currentDay, JSON_THROW_ON_ERROR),
        );
    }
}
