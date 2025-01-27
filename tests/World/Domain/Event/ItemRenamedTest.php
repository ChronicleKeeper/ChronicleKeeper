<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Domain\Event;

use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\Event\ItemRenamed;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ItemRenamed::class)]
#[Small]
final class ItemRenamedTest extends TestCase
{
    #[Test]
    public function itIsConstructable(): void
    {
        $item  = self::createStub(Item::class);
        $event = new ItemRenamed($item, 'Old Name');

        self::assertSame($item, $event->item);
        self::assertSame('Old Name', $event->oldName);
    }
}
