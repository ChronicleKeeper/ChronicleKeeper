<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Domain\Entity;

use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\Event\ItemChangedDescription;
use ChronicleKeeper\World\Domain\Event\ItemCreated;
use ChronicleKeeper\World\Domain\Event\ItemRenamed;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use ChronicleKeeper\World\Domain\ValueObject\MediaReference;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Item::class)]
#[Small]
final class ItemTest extends TestCase
{
    #[Test]
    public function itIsConstructable(): void
    {
        $item                                 = new Item(
            id: 'bd197c47-cad9-4e9a-b900-f3d79f64f272',
            type: ItemType::COUNTRY,
            name: 'Far Far Away',
            shortDescription: 'It is a mysterious country where all wishes come true.',
            mediaReferences: $mediaReferences = [self::createStub(MediaReference::class)],
        );

        self::assertSame('bd197c47-cad9-4e9a-b900-f3d79f64f272', $item->getId());
        self::assertSame(ItemType::COUNTRY, $item->getType());
        self::assertSame('Far Far Away', $item->getName());
        self::assertSame('It is a mysterious country where all wishes come true.', $item->getShortDescription());
        self::assertSame($mediaReferences, $item->getMediaReferences());

        // Check There are no events on construction
        self::assertEmpty($item->flushEvents());
    }

    #[Test]
    public function itCanNotBeCreatedWithAnInvalidIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The identifier of the item has to be an UUID.');

        new Item(
            id: 'invalid-uuid',
            type: ItemType::COUNTRY,
            name: 'Far Far Away',
            shortDescription: 'It is a mysterious country where all wishes come true.',
        );
    }

    #[Test]
    public function itIsCreatable(): void
    {
        $item = Item::create(
            type: ItemType::COUNTRY,
            name: 'Far Far Away',
            shortDescription: 'It is a mysterious country where all wishes come true.',
        );

        self::assertSame(ItemType::COUNTRY, $item->getType());
        self::assertSame('Far Far Away', $item->getName());
        self::assertSame('It is a mysterious country where all wishes come true.', $item->getShortDescription());

        // Check There is an event on creation
        $events = $item->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ItemCreated::class, $events[0]);
    }

    #[Test]
    public function itCanBeRenamed(): void
    {
        $item = (new ItemBuilder())->build();
        $item->rename('Far Far Away 2');

        self::assertSame('Far Far Away 2', $item->getName());

        // Check There is an event on renaming
        $events = $item->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ItemRenamed::class, $events[0]);
    }

    #[Test]
    public function itWillDoNothingOnChangingToIdenticalName(): void
    {
        $item = (new ItemBuilder())->build();
        $item->rename($item->getName());

        // Check There are no events on renaming
        self::assertEmpty($item->flushEvents());
    }

    #[Test]
    public function itCanBeDescribed(): void
    {
        $item = (new ItemBuilder())->build();
        $item->changeShortDescription('A not so beautiful country.');

        self::assertSame('A not so beautiful country.', $item->getShortDescription());

        // Check There is an event on describing
        $events = $item->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ItemChangedDescription::class, $events[0]);
    }

    #[Test]
    public function itIsDoingNothingOnChangingTheExactSameDescription(): void
    {
        $item = (new ItemBuilder())->withShortDescription('Foo Bar Baz')->build();
        $item->changeShortDescription($item->getShortDescription());

        // Check There are no events on describing
        self::assertEmpty($item->flushEvents());
    }

    #[Test]
    public function itCanAddMediaReferences(): void
    {
        $item = Item::create(
            type: ItemType::COUNTRY,
            name: 'Far Far Away',
            shortDescription: 'It is a mysterious country where all wishes come true.',
        );

        $mediaReference = self::createStub(MediaReference::class);

        $item->addMediaReference($mediaReference);

        self::assertCount(1, $item->getMediaReferences());
        self::assertSame($mediaReference, $item->getMediaReferences()[0]);
    }
}
