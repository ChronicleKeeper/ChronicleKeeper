<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\ValueObject;

use ChronicleKeeper\Calendar\Domain\ValueObject\Epoch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Epoch::class)]
#[Small]
final class EpochTest extends TestCase
{
    #[Test]
    public function itIsCreatableFromArray(): void
    {
        $epoch = Epoch::fromArray([
            'name' => 'Test Era',
            'startYear' => 42,
        ]);

        self::assertSame('Test Era', $epoch->name);
        self::assertSame(42, $epoch->beginsInYear);
        self::assertNull($epoch->endsInYear);
    }
}
