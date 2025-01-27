<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Controller;

use ChronicleKeeper\Test\WebTestCase;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Command\RemoveItemRelation;
use ChronicleKeeper\World\Application\Command\RemoveItemRelationHandler;
use ChronicleKeeper\World\Application\Command\StoreItemRelation;
use ChronicleKeeper\World\Application\Command\StoreWorldItem;
use ChronicleKeeper\World\Application\Query\FindRelationsOfItem;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use ChronicleKeeper\World\Presentation\Controller\WorldItemRelationRemove;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(WorldItemRelationRemove::class)]
#[CoversClass(RemoveItemRelation::class)]
#[CoversClass(RemoveItemRelationHandler::class)]
#[Large]
class WorldItemRelationRemoveTest extends WebTestCase
{
    private Item $sourceItem;
    private Item $targetItem;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sourceItem = (new ItemBuilder())
            ->withName('Source Item')
            ->withType(ItemType::COUNTRY)
            ->build();

        $this->targetItem = (new ItemBuilder())
            ->withName('Target Item')
            ->withType(ItemType::REGION)
            ->build();

        $this->bus->dispatch(new StoreWorldItem($this->sourceItem));
        $this->bus->dispatch(new StoreWorldItem($this->targetItem));
        $this->bus->dispatch(new StoreItemRelation(
            $this->sourceItem->getId(),
            $this->targetItem->getId(),
            'related',
        ));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sourceItem, $this->targetItem);
    }

    #[Test]
    public function itRemovesRelationFromItem(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/' . $this->sourceItem->getId() . '/relation/' . $this->targetItem->getId() . '/related/remove',
        );

        self::assertResponseRedirects('/world/item/' . $this->sourceItem->getId());
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Die Beziehung wurde erfolgreich entfernt.');

        $relations = $this->queryService->query(new FindRelationsOfItem($this->sourceItem->getId()));
        self::assertIsArray($relations);
        self::assertCount(0, $relations);
    }
}
