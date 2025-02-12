<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Library\Application\Query\FindDirectoryContent;
use ChronicleKeeper\Library\Application\Query\FindDirectoryContentQuery;
use ChronicleKeeper\Library\Presentation\Controller\Library;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(Library::class)]
#[CoversClass(FindDirectoryContent::class)]
#[CoversClass(FindDirectoryContentQuery::class)]
#[Large]
final class LibraryTest extends WebTestCase
{
    #[Test]
    public function itRendersAnEmptyList(): void
    {
        $this->client->request(Request::METHOD_GET, '/library');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Bibliothek');
        self::assertSelectorTextContains('td', 'Es wurden noch keine Dateien geladen.');
    }

    #[Test]
    public function itRendersDirectoryContent(): void
    {
        // -------------- Setup Data Fixtures --------------

        $image = (new ImageBuilder())->build();
        $this->bus->dispatch(new StoreImage($image));

        $document = (new DocumentBuilder())->build();
        $this->bus->dispatch(new StoreDocument($document));

        $conversation = (new ConversationBuilder())->build();
        $this->bus->dispatch(new StoreConversation($conversation));

        $directory = (new DirectoryBuilder())->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        // -------------- Execute Test --------------

        $this->client->request(Request::METHOD_GET, '/library');

        // -------------- Asserts --------------

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Bibliothek');
        self::assertSelectorTextContains('td', $directory->getTitle());
        self::assertSelectorTextContains('td', $image->getTitle());
        self::assertSelectorTextContains('td', $document->getTitle());
        self::assertSelectorTextContains('td', $conversation->getTitle());
    }

    #[Test]
    public function itIsAbleToRenderAnEmptySubDirectory(): void
    {
        // -------------- Setup Data Fixtures --------------

        $directory = (new DirectoryBuilder())->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        // -------------- Execute Test --------------

        $this->client->request(Request::METHOD_GET, '/library/' . $directory->getId());

        // -------------- Asserts --------------

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Bibliothek');
        self::assertSelectorTextContains('li.active', $directory->getTitle());

        self::assertSelectorTextContains('tbody tr:first-child', '...');
        self::assertSelectorTextContains('tbody tr:last-child', 'Es wurden noch keine Dateien geladen.');
    }

    #[Test]
    public function itRendersContentOfSubDirectory(): void
    {
        // -------------- Setup Data Fixtures --------------

        $directory = (new DirectoryBuilder())->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        $image = (new ImageBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreImage($image));

        $document = (new DocumentBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreDocument($document));

        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreConversation($conversation));

        // -------------- Execute Test --------------

        $this->client->request(Request::METHOD_GET, '/library/' . $directory->getId());

        // -------------- Asserts --------------

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Bibliothek');
        self::assertSelectorTextContains('li.active', $directory->getTitle());

        self::assertSelectorTextContains('tbody tr:nth-child(1)', '...');
        self::assertSelectorTextContains('tbody tr:nth-child(2)', $image->getTitle());
        self::assertSelectorTextContains('tbody tr:nth-child(3)', $document->getTitle());
        self::assertSelectorTextContains('tbody tr:nth-child(4)', $conversation->getTitle());
    }
}
