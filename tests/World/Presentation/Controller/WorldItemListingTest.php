<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Controller;

use ChronicleKeeper\Test\WebTestCase;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Query\SearchWorldItems;
use ChronicleKeeper\World\Application\Query\SearchWorldItemsCount;
use ChronicleKeeper\World\Application\Query\SearchWorldItemsCountQuery;
use ChronicleKeeper\World\Application\Query\SearchWorldItemsQuery;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use ChronicleKeeper\World\Presentation\Controller\WorldItemListing;
use ChronicleKeeper\World\Presentation\Twig\ItemSearch;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(WorldItemListing::class)]
#[CoversClass(SearchWorldItems::class)]
#[CoversClass(SearchWorldItemsQuery::class)]
#[CoversClass(ItemSearch::class)]
#[CoversClass(SearchWorldItemsCount::class)]
#[CoversClass(SearchWorldItemsCountQuery::class)]
#[Large]
class WorldItemListingTest extends WebTestCase
{
    private Item $item;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->item = (new ItemBuilder())
            ->withName('Test Item')
            ->withType(ItemType::COUNTRY)
            ->build();

        // Check if item already exists
        $existingItem = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('world_items')
            ->where('id = :id')
            ->setParameter('id', $this->item->getId())
            ->executeQuery()
            ->fetchAssociative();

        if ($existingItem !== false) {
            // Update existing item
            $this->connection->update(
                'world_items',
                [
                    'name' => $this->item->getName(),
                    'type' => $this->item->getType()->value,
                    'short_description' => $this->item->getShortDescription(),
                ],
                ['id' => $this->item->getId()],
            );
        } else {
            // Insert new item
            $this->connection->insert('world_items', [
                'id' => $this->item->getId(),
                'name' => $this->item->getName(),
                'type' => $this->item->getType()->value,
                'short_description' => $this->item->getShortDescription(),
            ]);
        }
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->item);
    }

    #[Test]
    public function itRendersTheItemList(): void
    {
        $this->client->request(Request::METHOD_GET, '/world');

        self::assertResponseIsSuccessful();

        self::assertSelectorExists('table#item-list > tbody > tr.result-row');
        self::assertSelectorTextContains('table#item-list > tbody > tr.result-row', 'Test Item');
    }
}
