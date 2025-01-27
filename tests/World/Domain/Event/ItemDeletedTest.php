<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Domain\Event;

use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\Event\ItemDeleted;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ItemDeleted::class)]
#[Small]
final class ItemDeletedTest extends TestCase
{
    #[Test]
    public function itIsConstructable(): void
    {
        $item  = self::createStub(Item::class);
        $event = new ItemDeleted($item);

        self::assertSame($item, $event->item);
    }
}
