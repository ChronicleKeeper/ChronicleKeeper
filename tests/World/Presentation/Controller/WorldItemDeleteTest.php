<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Controller;

use ChronicleKeeper\Test\WebTestCase;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Command\DeleteWorldItem;
use ChronicleKeeper\World\Application\Command\DeleteWorldItemHandler;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Presentation\Controller\WorldItemDelete;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(WorldItemDelete::class)]
#[CoversClass(DeleteWorldItem::class)]
#[CoversClass(DeleteWorldItemHandler::class)]
#[Large]
class WorldItemDeleteTest extends WebTestCase
{
    private Item $item;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->item = (new ItemBuilder())
            ->withName('Test Item')
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
    public function itRendersConfirmationMessage(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/' . $this->item->getId() . '/delete',
        );

        self::assertResponseRedirects('/world/item/' . $this->item->getId());
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-warning', 'Das Löschen des Eintrags muss erst bestätigt werden!');
    }

    #[Test]
    public function itDeletesTheItem(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/' . $this->item->getId() . '/delete',
            ['confirm' => 1],
        );

        self::assertResponseRedirects('/world');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Der Eintrag wurde erfolgreich gelöscht.');

        $items = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('world_items')
            ->where('id = :id')
            ->setParameter('id', $this->item->getId())
            ->executeQuery()
            ->fetchAllAssociative();

        self::assertEmpty($items);
    }

    #[Test]
    public function itCanDeleteAnItemWithRelations(): void
    {
        $anotherItem = (new ItemBuilder())->withName('Test Item 2')->build();

        // Check if item already exists
        $existingItem = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('world_items')
            ->where('id = :id')
            ->setParameter('id', $anotherItem->getId())
            ->executeQuery()
            ->fetchAssociative();

        if ($existingItem !== false) {
            // Update existing item
            $this->connection->update(
                'world_items',
                [
                    'name' => $anotherItem->getName(),
                    'type' => $anotherItem->getType()->value,
                    'short_description' => $anotherItem->getShortDescription(),
                ],
                ['id' => $anotherItem->getId()],
            );
        } else {
            // Insert new item
            $this->connection->insert('world_items', [
                'id' => $anotherItem->getId(),
                'name' => $anotherItem->getName(),
                'type' => $anotherItem->getType()->value,
                'short_description' => $anotherItem->getShortDescription(),
            ]);
        }

        // Check if relation already exists
        $existingRelation = $this->connection->createQueryBuilder()
            ->select('source_world_item_id')
            ->from('world_item_relations')
            ->where('source_world_item_id = :source_id')
            ->andWhere('target_world_item_id = :target_id')
            ->setParameter('source_id', $this->item->getId())
            ->setParameter('target_id', $anotherItem->getId())
            ->executeQuery()
            ->fetchAssociative();

        if ($existingRelation === false) {
            // Only insert if relation doesn't exist
            $this->connection->insert('world_item_relations', [
                'relation_type' => 'related',
                'source_world_item_id' => $this->item->getId(),
                'target_world_item_id' => $anotherItem->getId(),
            ]);
        }

        $this->client->request(
            Request::METHOD_GET,
            '/world/item/' . $this->item->getId() . '/delete',
            ['confirm' => 1],
        );

        self::assertResponseRedirects('/world');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Der Eintrag wurde erfolgreich gelöscht.');

        $sourceRelations = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('world_item_relations')
            ->where('source_world_item_id = :id')
            ->orWhere('target_world_item_id = :id')
            ->setParameter('id', $this->item->getId())
            ->executeQuery()
            ->fetchAllAssociative();

        self::assertEmpty($sourceRelations);

        $targetRelations = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('world_item_relations')
            ->where('source_world_item_id = :id')
            ->orWhere('target_world_item_id = :id')
            ->setParameter('id', $anotherItem->getId())
            ->executeQuery()
            ->fetchAllAssociative();

        self::assertEmpty($targetRelations);
    }
}
