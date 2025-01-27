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
use ChronicleKeeper\World\Application\Command\RemoveWorldItemMedium;
use ChronicleKeeper\World\Application\Command\RemoveWorldItemMediumHandler;
use ChronicleKeeper\World\Application\Command\StoreWorldItem;
use ChronicleKeeper\World\Application\Query\GetWorldItem;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ConversationReference;
use ChronicleKeeper\World\Domain\ValueObject\DocumentReference;
use ChronicleKeeper\World\Domain\ValueObject\ImageReference;
use ChronicleKeeper\World\Domain\ValueObject\MediaReference;
use ChronicleKeeper\World\Presentation\Controller\WorldItemMediaRemove;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function array_map;

#[CoversClass(WorldItemMediaRemove::class)]
#[CoversClass(RemoveWorldItemMedium::class)]
#[CoversClass(RemoveWorldItemMediumHandler::class)]
#[Large]
class WorldItemMediaRemoveTest extends WebTestCase
{
    private Item $item;
    private Document $document;
    private Image $image;
    private Conversation $conversation;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->item = (new ItemBuilder())->build();
        $this->bus->dispatch(new StoreWorldItem($this->item));

        $this->document = (new DocumentBuilder())->build();
        $this->bus->dispatch(new StoreDocument($this->document));

        $this->image = (new ImageBuilder())->build();
        $this->bus->dispatch(new StoreImage($this->image));

        $this->conversation = (new ConversationBuilder())->build();
        $this->bus->dispatch(new StoreConversation($this->conversation));

        $this->item->addMediaReference(new DocumentReference($this->item, $this->document));
        $this->item->addMediaReference(new ImageReference($this->item, $this->image));
        $this->item->addMediaReference(new ConversationReference($this->item, $this->conversation));
        $this->bus->dispatch(new StoreWorldItem($this->item));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->item);
    }

    #[Test]
    public function itRemovedDocumentReference(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/' . $this->item->getId() . '/remove_medium/document_' . $this->document->getId(),
        );

        self::assertResponseRedirects('/world/item/' . $this->item->getId());
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Die Verlinkung wurden erfolgreich aufgehoben.');

        $item = $this->queryService->query(new GetWorldItem($this->item->getId()));
        self::assertNotNull($item);
        self::assertCount(2, $item->getMediaReferences());

        $existingMediaTypes = array_map(
            static fn (MediaReference $mediaReference) => $mediaReference->getGenericLinkIdentifier(),
            $item->getMediaReferences(),
        );

        self::assertContains('image_' . $this->image->getId(), $existingMediaTypes);
        self::assertContains('conversation_' . $this->conversation->getId(), $existingMediaTypes);
        self::assertNotContains('document_' . $this->document->getId(), $existingMediaTypes);
    }

    #[Test]
    public function itRemovedImageReference(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/' . $this->item->getId() . '/remove_medium/image_' . $this->image->getId(),
        );

        self::assertResponseRedirects('/world/item/' . $this->item->getId());
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Die Verlinkung wurden erfolgreich aufgehoben.');

        $item = $this->queryService->query(new GetWorldItem($this->item->getId()));
        self::assertNotNull($item);
        self::assertCount(2, $item->getMediaReferences());

        $existingMediaTypes = array_map(
            static fn (MediaReference $mediaReference) => $mediaReference->getGenericLinkIdentifier(),
            $item->getMediaReferences(),
        );

        self::assertNotContains('image_' . $this->image->getId(), $existingMediaTypes);
        self::assertContains('conversation_' . $this->conversation->getId(), $existingMediaTypes);
        self::assertContains('document_' . $this->document->getId(), $existingMediaTypes);
    }

    #[Test]
    public function itRemovedConversationReference(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/' . $this->item->getId() . '/remove_medium/conversation_' . $this->conversation->getId(),
        );

        self::assertResponseRedirects('/world/item/' . $this->item->getId());
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Die Verlinkung wurden erfolgreich aufgehoben.');

        $item = $this->queryService->query(new GetWorldItem($this->item->getId()));
        self::assertNotNull($item);
        self::assertCount(2, $item->getMediaReferences());

        $existingMediaTypes = array_map(
            static fn (MediaReference $mediaReference) => $mediaReference->getGenericLinkIdentifier(),
            $item->getMediaReferences(),
        );

        self::assertContains('image_' . $this->image->getId(), $existingMediaTypes);
        self::assertNotContains('conversation_' . $this->conversation->getId(), $existingMediaTypes);
        self::assertContains('document_' . $this->document->getId(), $existingMediaTypes);
    }

    #[Test]
    public function itHandlesInvalidMediaIdentifier(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/' . $this->item->getId() . '/remove_medium/uuid_' . $this->document->getId(),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        self::assertStringContainsString(
            'The medium type has to be either',
            (string) $this->client->getResponse()->getContent(),
        );
    }
}
