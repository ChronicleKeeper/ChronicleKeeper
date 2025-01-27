<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Controller;

use ChronicleKeeper\Test\WebTestCase;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Command\StoreWorldItem;
use ChronicleKeeper\World\Application\Query\FindRelationsOfItem;
use ChronicleKeeper\World\Application\Query\FindRelationsOfItemQuery;
use ChronicleKeeper\World\Application\Query\GetWorldItem;
use ChronicleKeeper\World\Application\Query\GetWorldItemQuery;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Presentation\Controller\WorldItemView;
use ChronicleKeeper\World\Presentation\Twig\AddRelationToWorld;
use ChronicleKeeper\World\Presentation\Twig\WorldItemRelations;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(WorldItemView::class)]
#[CoversClass(WorldItemRelations::class)]
#[CoversClass(AddRelationToWorld::class)]
#[CoversClass(GetWorldItem::class)]
#[CoversClass(GetWorldItemQuery::class)]
#[CoversClass(FindRelationsOfItem::class)]
#[CoversClass(FindRelationsOfItemQuery::class)]
#[Large]
class WorldItemViewTest extends WebTestCase
{
    private Item $item;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->item = (new ItemBuilder())->build();

        $this->bus->dispatch(new StoreWorldItem($this->item));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->item);
    }

    #[Test]
    public function itRendersTheItemView(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/' . $this->item->getId(),
        );

        self::assertResponseIsSuccessful();

        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString($this->item->getName(), $content);
    }
}
