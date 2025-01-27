<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Controller;

use ChronicleKeeper\Test\WebTestCase;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Query\SearchWorldItems;
use ChronicleKeeper\World\Application\Query\SearchWorldItemsQuery;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use ChronicleKeeper\World\Presentation\Controller\WorldItemAutocomplete;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(WorldItemAutocomplete::class)]
#[CoversClass(SearchWorldItems::class)]
#[CoversClass(SearchWorldItemsQuery::class)]
#[Large]
class WorldItemAutocompleteTest extends WebTestCase
{
    private Item $searchableItem;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->searchableItem = (new ItemBuilder())
            ->withName('Far Far Away')
            ->withType(ItemType::COUNTRY)
            ->build();

        $this->databasePlatform->insertOrUpdate('world_items', [
            'id' => $this->searchableItem->getId(),
            'name' => $this->searchableItem->getName(),
            'type' => $this->searchableItem->getType()->value,
            'short_description' => $this->searchableItem->getShortDescription(),
        ]);
    }

    #[Override]
    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->searchableItem);
    }

    #[Test]
    public function itReturnsEmptyResultsForEmptyQuery(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/bd197c47-cad9-4e9a-b900-f3d79f64f272/autocomplete',
            ['query' => ''],
        );

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            '{"results":[]}',
            (string) $this->client->getResponse()->getContent(),
        );
    }

    #[Test]
    public function itReturnsResultsForValidQuery(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/bd197c47-cad9-4e9a-b900-f3d79f64f272/autocomplete',
            ['query' => 'Far'],
        );

        self::assertResponseIsSuccessful();
        self::assertStringContainsString(
            'Far Far Away (Land)',
            (string) $this->client->getResponse()->getContent(),
        );
    }

    #[Test]
    public function itReturnsEmptyResultsForNonMatchingQuery(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/bd197c47-cad9-4e9a-b900-f3d79f64f272/autocomplete',
            ['query' => 'Nonexistent'],
        );

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            '{"results":[]}',
            (string) $this->client->getResponse()->getContent(),
        );
    }
}
