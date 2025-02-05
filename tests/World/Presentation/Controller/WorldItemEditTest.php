<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Controller;

use ChronicleKeeper\Test\WebTestCase;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Command\StoreWorldItem;
use ChronicleKeeper\World\Application\Command\StoreWorldItemHandler;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use ChronicleKeeper\World\Presentation\Controller\WorldItemEdit;
use ChronicleKeeper\World\Presentation\Form\WorldItemType;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(WorldItemEdit::class)]
#[CoversClass(StoreWorldItem::class)]
#[CoversClass(StoreWorldItemHandler::class)]
#[CoversClass(WorldItemType::class)]
#[Large]
class WorldItemEditTest extends WebTestCase
{
    private Item $item;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->item = (new ItemBuilder())
            ->withName('Test Item')
            ->withType(ItemType::WEAPON)
            ->withShortDescription('A description')
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
    public function itRendersTheEditForm(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/' . $this->item->getId() . '/edit',
        );

        self::assertResponseIsSuccessful();

        self::assertFormValue('form', 'world_item[name]', $this->item->getName());
        self::assertFormValue('form', 'world_item[shortDescription]', $this->item->getShortDescription());
    }

    #[Test]
    public function itEditsTheItem(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/world/item/' . $this->item->getId() . '/edit',
            [
                'world_item' => [
                    'name' => 'Updated Item',
                    'type' => 'country',
                    'shortDescription' => 'Updated Description',
                ],
            ],
        );

        self::assertResponseRedirects('/world/item/' . $this->item->getId());
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Der Eintrag wurde erfolgreich bearbeitet.');

        $updatedItem = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->from('world_items')
            ->where('id', '=', $this->item->getId())
            ->fetchOne();

        self::assertSame('Updated Item', $updatedItem['name']);
        self::assertSame('Updated Description', $updatedItem['short_description']);
    }

    #[Test]
    public function itHandlesFormValidationErrors(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/world/item/' . $this->item->getId() . '/edit',
            ['world_item' => ['name' => '']],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextSame('div.invalid-feedback', 'This value should not be blank.');
    }
}
