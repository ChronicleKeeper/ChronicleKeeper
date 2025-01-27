<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Domain\Event;

use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\Event\ItemChangedDescription;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ItemChangedDescription::class)]
#[Small]
final class ItemChangedDescriptionTest extends TestCase
{
    #[Test]
    public function itIsConstructable(): void
    {
        $item           = self::createStub(Item::class);
        $oldDescription = 'Old Description';

        $event = new ItemChangedDescription($item, $oldDescription);

        self::assertSame($item, $event->item);
        self::assertSame($oldDescription, $event->oldDescription);
    }
}
