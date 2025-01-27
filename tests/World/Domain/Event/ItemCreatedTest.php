<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Domain\Event;

use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\Event\ItemCreated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ItemCreated::class)]
#[Small]
final class ItemCreatedTest extends TestCase
{
    #[Test]
    public function itIsConstructable(): void
    {
        $item  = self::createStub(Item::class);
        $event = new ItemCreated($item);

        self::assertSame($item, $event->item);
    }
}
