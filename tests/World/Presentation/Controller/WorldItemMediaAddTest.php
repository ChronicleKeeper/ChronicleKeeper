<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\WebTestCase;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Command\StoreWorldItem;
use ChronicleKeeper\World\Application\Command\StoreWorldItemHandler;
use ChronicleKeeper\World\Application\Query\GetWorldItem;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Presentation\Controller\WorldItemMediaAdd;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(WorldItemMediaAdd::class)]
#[CoversClass(StoreWorldItem::class)]
#[CoversClass(StoreWorldItemHandler::class)]
#[Large]
class WorldItemMediaAddTest extends WebTestCase
{
    private Item $item;
    private Conversation $conversation;
    private Document $document;
    private Image $image;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->item = (new ItemBuilder())->build();
        $this->bus->dispatch(new StoreWorldItem($this->item));

        $this->conversation = (new ConversationBuilder())->build();
        $this->bus->dispatch(new StoreConversation($this->conversation));

        $this->document = (new DocumentBuilder())->build();
        $this->bus->dispatch(new StoreDocument($this->document));

        $this->image = (new ImageBuilder())->build();
        $this->bus->dispatch(new StoreImage($this->image));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->item);
    }

    #[Test]
    public function itRendersTheMediaAddForm(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/' . $this->item->getId() . '/add_relations',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('select[name="media[]"]');
    }

    #[Test]
    public function itAddsMediaReferencesToItem(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/world/item/' . $this->item->getId() . '/add_relations',
            [
                'media' => [
                    'image_' . $this->image->getId(),
                    'conversation_' . $this->conversation->getId(),
                    'document_' . $this->document->getId(),
                ],
            ],
        );

        self::assertResponseRedirects('/world/item/' . $this->item->getId());
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Die Medien wurden erfolgreich verlinkt.');

        $updatedItem = $this->queryService->query(new GetWorldItem($this->item->getId()));

        self::assertCount(3, $updatedItem->getMediaReferences());
    }

    #[Test]
    public function itDoesFailWithAnException(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/world/item/' . $this->item->getId() . '/add_relations',
            ['media' => ['foo_bar']],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        self::assertStringContainsString(
            'Unknown media type.',
            (string) $this->client->getResponse()->getContent(),
        );
    }
}
