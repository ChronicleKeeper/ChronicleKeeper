<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Domain\ValueObject;

use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Domain\ValueObject\Relation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Relation::class)]
#[Small]
final class RelationTest extends TestCase
{
    #[Test]
    public function itIsConstructable(): void
    {
        $item     = (new ItemBuilder())->build();
        $relation = new Relation($item, 'allied');

        self::assertSame($item, $relation->toItem);
        self::assertSame('allied', $relation->relationType);
    }

    #[Test]
    public function itReturnsCorrectToItem(): void
    {
        $item     = (new ItemBuilder())->build();
        $relation = new Relation($item, 'allied');

        self::assertSame($item, $relation->toItem);
    }

    #[Test]
    public function itReturnsCorrectRelationType(): void
    {
        $item     = (new ItemBuilder())->build();
        $relation = new Relation($item, 'allied');

        self::assertSame('allied', $relation->relationType);
    }
}
