<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Controller;

use ChronicleKeeper\Test\WebTestCase;
use ChronicleKeeper\World\Application\Command\StoreWorldItem;
use ChronicleKeeper\World\Application\Command\StoreWorldItemHandler;
use ChronicleKeeper\World\Presentation\Controller\WorldItemCreate;
use ChronicleKeeper\World\Presentation\Form\WorldItemType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(WorldItemCreate::class)]
#[CoversClass(StoreWorldItem::class)]
#[CoversClass(StoreWorldItemHandler::class)]
#[CoversClass(WorldItemType::class)]
#[Large]
class WorldItemCreateTest extends WebTestCase
{
    #[Test]
    public function itRendersTheForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/world/create_item');

        self::assertResponseIsSuccessful();

        self::assertFormValue('form', 'world_item[name]', '');
        self::assertFormValue('form', 'world_item[type]', 'person');
    }

    #[Test]
    public function itCreatesANewItem(): void
    {
        $this->client->request(Request::METHOD_POST, '/world/create_item', [
            'world_item' => [
                'name' => 'New Item',
                'type' => 'country',
            ],
        ]);

        self::assertResponseRedirects('/world');

        // Check the new document is stored
        $items = $this->databasePlatform->fetch('SELECT * FROM world_items');
        self::assertCount(1, $items);

        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Der Eintrag wurde erfolgreich erstellt.');
    }

    #[Test]
    public function itHandlesFormValidationErrors(): void
    {
        $this->client->request(Request::METHOD_POST, '/world/create_item', [
            'world_item' => ['name' => '', 'type' => 'weapon'],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertSelectorTextSame('div.invalid-feedback', 'This value should not be blank.');
        self::assertFormValue('form', 'world_item[type]', 'weapon');
    }
}
