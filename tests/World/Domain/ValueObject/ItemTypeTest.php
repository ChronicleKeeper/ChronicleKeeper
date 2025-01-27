<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Domain\ValueObject;

use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ItemType::class)]
#[Small]
final class ItemTypeTest extends TestCase
{
    #[Test]
    public function itReturnsCorrectLabel(): void
    {
        self::assertSame('Land', ItemType::COUNTRY->getLabel());
        self::assertSame('Person', ItemType::PERSON->getLabel());
        self::assertSame('Waffe', ItemType::WEAPON->getLabel());
    }

    #[Test]
    public function itIdentifiesObjectTypesCorrectly(): void
    {
        self::assertTrue(ItemType::OBJECT->isObjectType());
        self::assertTrue(ItemType::ARTIFACT->isObjectType());
        self::assertFalse(ItemType::COUNTRY->isObjectType());
    }

    #[Test]
    public function itReturnsGroupedTypes(): void
    {
        $groupedTypes = ItemType::getGroupedTypes();
        self::assertArrayHasKey('Geografisch & Politisch', $groupedTypes);
        self::assertContains(ItemType::COUNTRY, $groupedTypes['Geografisch & Politisch']);
    }

    #[Test]
    public function itReturnsPossibleRelationsForType(): void
    {
        $relations = ItemType::getPossibleRelationsForType(ItemType::COUNTRY);

        self::assertContains('country', $relations);
        self::assertContains('campaign', $relations);
    }

    #[Test]
    public function itReturnsRelationLabelTo(): void
    {
        $label = ItemType::COUNTRY->getRelationLabelTo(ItemType::COUNTRY, 'allied');
        self::assertSame('Verbündeter mit', $label);
    }

    #[Test]
    public function itReturnsDefaultRelatedRelation(): void
    {
        $label = ItemType::COUNTRY->getRelationLabelTo(ItemType::PERSON, 'related');
        self::assertSame('Beziehung zu', $label);
    }

    #[Test]
    public function itReturnsObjectSubTypes(): void
    {
        $subTypes = ItemType::getObjectSubTypes();
        self::assertContains(ItemType::ARTIFACT, $subTypes);
        self::assertContains(ItemType::WEAPON, $subTypes);
        self::assertContains(ItemType::DOCUMENT, $subTypes);
    }
}
