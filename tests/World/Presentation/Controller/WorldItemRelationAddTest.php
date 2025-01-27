<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Controller;

use ChronicleKeeper\Test\WebTestCase;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Command\StoreItemRelation;
use ChronicleKeeper\World\Application\Command\StoreItemRelationHandler;
use ChronicleKeeper\World\Application\Command\StoreWorldItem;
use ChronicleKeeper\World\Application\Query\FindRelationsOfItem;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\Relation;
use ChronicleKeeper\World\Presentation\Controller\WorldItemRelationAdd;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(WorldItemRelationAdd::class)]
#[CoversClass(StoreItemRelation::class)]
#[CoversClass(StoreItemRelationHandler::class)]
#[Large]
class WorldItemRelationAddTest extends WebTestCase
{
    private Item $fromItem;
    private Item $toItem;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fromItem = (new ItemBuilder())->build();
        $this->bus->dispatch(new StoreWorldItem($this->fromItem));

        $this->toItem = (new ItemBuilder())->build();
        $this->bus->dispatch(new StoreWorldItem($this->toItem));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->fromItem);
    }

    #[Test]
    public function itAddsRelationToItem(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/world/item/' . $this->fromItem->getId() . '/relation/add',
            [
                'item_relation' => [
                    'world_item' => $this->toItem->getId(),
                    'type' => 'related',
                ],
            ],
        );

        self::assertResponseRedirects('/world/item/' . $this->fromItem->getId());
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Die Beziehung wurde erfolgreich hinzugefügt.');

        $relations = $this->queryService->query(new FindRelationsOfItem($this->fromItem->getId()));
        self::assertIsArray($relations);
        self::assertCount(1, $relations);

        $relation = $relations[0];
        self::assertInstanceOf(Relation::class, $relation);
        self::assertSame($this->toItem->getId(), $relation->toItem->getId());
        self::assertSame('related', $relation->relationType);
    }

    #[Test]
    public function itHandlesMissingRelationData(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/world/item/' . $this->fromItem->getId() . '/relation/add',
            [
                'item_relation' => [
                    'world_item' => '',
                    'type' => '',
                ],
            ],
        );

        self::assertResponseRedirects('/world/item/' . $this->fromItem->getId());
        $this->client->followRedirect();
    }

    #[Test]
    public function itHandlesMissingQueryData(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/world/item/' . $this->fromItem->getId() . '/relation/add',
        );

        self::assertResponseRedirects('/world/item/' . $this->fromItem->getId());
        $this->client->followRedirect();
    }
}
