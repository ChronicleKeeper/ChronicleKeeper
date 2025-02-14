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

        $this->databasePlatform->createQueryBuilder()->createInsert()
            ->insert('world_items')
            ->values([
                'id' => $this->item->getId(),
                'name' => $this->item->getName(),
                'type' => $this->item->getType()->value,
                'short_description' => $this->item->getShortDescription(),
            ])
            ->execute();
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

        $items = $this->databasePlatform->fetch(
            'SELECT * FROM world_items WHERE id = :id',
            ['id' => $this->item->getId()],
        );
        self::assertEmpty($items);
    }
}
