<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Form;

use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\Event\ItemChangedDescription;
use ChronicleKeeper\World\Domain\Event\ItemCreated;
use ChronicleKeeper\World\Domain\Event\ItemRenamed;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use ChronicleKeeper\World\Presentation\Form\WorldItemType;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

#[CoversClass(WorldItemType::class)]
#[Small]
final class WorldItemTypeTest extends TypeTestCase
{
    #[Test]
    public function itCreatesANewWorldItem(): void
    {
        $form = $this->factory->create(WorldItemType::class);
        $form->submit([
            'type' => 'location',
            'name' => 'My Location',
            'shortDescription' => 'This is a description of the location.',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());

        $item = $form->getData();
        self::assertInstanceOf(Item::class, $item);
        self::assertSame(ItemType::LOCATION, $item->getType());
        self::assertSame('My Location', $item->getName());
        self::assertSame('This is a description of the location.', $item->getShortDescription());

        $events = $item->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ItemCreated::class, $events[0]);
    }

    #[Test]
    public function itCanEditAnExistingWorldItem(): void
    {
        $existingItem = (new ItemBuilder())->build();
        $form         = $this->factory->create(WorldItemType::class, $existingItem);
        $form->submit([
            'type' => 'quest', // Is not changeable
            'name' => 'Edited World Item',
            'shortDescription' => 'This is an edited world item.',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());

        $item = $form->getData();
        self::assertInstanceOf(Item::class, $item);
        self::assertSame(ItemType::PERSON, $item->getType());
        self::assertSame('Edited World Item', $item->getName());
        self::assertSame('This is an edited world item.', $item->getShortDescription());

        $events = $item->flushEvents();
        self::assertCount(2, $events);
        self::assertInstanceOf(ItemRenamed::class, $events[0]);
        self::assertInstanceOf(ItemChangedDescription::class, $events[1]);
    }

    #[Test]
    public function itValidatesTheForm(): void
    {
        $form = $this->factory->create(WorldItemType::class);
        $form->submit([
            'type' => 'person',
            'name' => '',
            'shortDescription' => '',
        ]);

        self::assertFalse($form->isValid());
        self::assertCount(1, $form->getErrors(true));

        self::assertSame(
            'This value should not be blank.',
            $form->get('name')->getErrors()[0]->getMessage(),
        );
    }

    /** @inheritDoc */
    #[Override]
    protected function getExtensions(): array
    {
        return [new ValidatorExtension(Validation::createValidator())];
    }
}
