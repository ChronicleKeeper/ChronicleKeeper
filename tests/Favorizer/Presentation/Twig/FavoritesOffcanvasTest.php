<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Presentation\Twig;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Favorizer\Domain\ValueObject\WorldItemTarget;
use ChronicleKeeper\Favorizer\Presentation\Twig\FavoritesOffcanvas;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

use function assert;
use function iterator_to_array;

#[CoversClass(FavoritesOffcanvas::class)]
#[Large]
class FavoritesOffcanvasTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    private Document $document;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->document = (new DocumentBuilder())->withTitle('Foo Bar Baz')->build();
        $this->bus->dispatch(new StoreDocument($this->document));

        $this->databasePlatform->createQueryBuilder()->createInsert()
            ->asReplace()
            ->insert('favorites')
            ->values([
                'id' => $this->document->getId(),
                'title' => $this->document->getTitle(),
                'type' => 'LibraryDocumentTarget',
            ])
            ->execute();
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->document);
    }

    #[Test]
    public function itRendersCorrectly(): void
    {
        $component = $this->createLiveComponent(
            name: FavoritesOffcanvas::class,
        );

        $renderedComponent = $component->render()->toString();

        self::assertStringContainsString('Favoriten', $renderedComponent);
        self::assertStringContainsString('Foo Bar Baz', $renderedComponent);
    }

    #[Test]
    public function itHandlesFavoritesUpdatedEvent(): void
    {
        $component = $this->createLiveComponent(
            name: FavoritesOffcanvas::class,
        );

        $component->emit('favorites_updated');

        $renderedComponent = $component->render()->toString();
        self::assertNotEmpty($renderedComponent);
    }

    #[Test]
    public function itGeneratesShortcutsCorrectly(): void
    {
        $component = $this->createLiveComponent(
            name: FavoritesOffcanvas::class,
        );

        $component = $component->component();
        assert($component instanceof FavoritesOffcanvas);

        $shortcuts = iterator_to_array($component->getShortcuts());

        self::assertNotEmpty($shortcuts);
        foreach ($shortcuts as $shortcut) {
            self::assertArrayHasKey('icon', $shortcut);
            self::assertArrayHasKey('title', $shortcut);
            self::assertArrayHasKey('url', $shortcut);
        }
    }

    #[Test]
    public function itGroupsShortcutsAlphabetically(): void
    {
        $component = $this->createLiveComponent(
            name: FavoritesOffcanvas::class,
        );

        $component = $component->component();
        assert($component instanceof FavoritesOffcanvas);

        $groupedShortcuts = $component->getAlphabeticallyGrouped();

        self::assertNotEmpty($groupedShortcuts);
        foreach ($groupedShortcuts as $letter => $shortcuts) {
            self::assertSame('F', $letter);
            self::assertNotEmpty($shortcuts);

            foreach ($shortcuts as $shortcut) {
                self::assertSame('tabler:file-search', $shortcut['icon']);
                self::assertSame('Foo Bar Baz', $shortcut['title']);
                self::assertStringContainsString($this->document->getId(), $shortcut['url']);
            }
        }
    }

    #[Test]
    public function itIsDeliveringTheCorrectIcons(): void
    {
        $component = $this->createLiveComponent(name: FavoritesOffcanvas::class);

        $component = $component->component();
        assert($component instanceof FavoritesOffcanvas);

        $reflection = new ReflectionClass($component);
        $iconMethod = $reflection->getMethod('getIconForTarget');

        $documentTarget = new LibraryDocumentTarget($this->document->getId(), $this->document->getTitle());
        self::assertSame('tabler:file-search', $iconMethod->invokeArgs($component, [$documentTarget]));

        $target = new LibraryImageTarget($this->document->getId(), $this->document->getTitle());
        self::assertSame('tabler:photo-search', $iconMethod->invokeArgs($component, [$target]));

        $target = new ChatConversationTarget($this->document->getId(), $this->document->getTitle());
        self::assertSame('tabler:message-2-share', $iconMethod->invokeArgs($component, [$target]));

        $target = new WorldItemTarget($this->document->getId(), $this->document->getTitle());
        self::assertSame('tabler:database-search', $iconMethod->invokeArgs($component, [$target]));

        $target = self::createStub(Target::class);
        self::assertSame('tabler:file', $iconMethod->invokeArgs($component, [$target]));
    }

    #[Test]
    public function itIsDeliveringTheShortcutUrl(): void
    {
        $component = $this->createLiveComponent(name: FavoritesOffcanvas::class);

        $component = $component->component();
        assert($component instanceof FavoritesOffcanvas);

        $reflection = new ReflectionClass($component);
        $iconMethod = $reflection->getMethod('generateShortcutUrl');

        $documentTarget = new LibraryDocumentTarget($this->document->getId(), $this->document->getTitle());
        self::assertSame(
            '/library/document/' . $this->document->getId(),
            $iconMethod->invokeArgs($component, [$documentTarget]),
        );

        $target = new LibraryImageTarget($this->document->getId(), $this->document->getTitle());
        self::assertSame(
            '/library/image/' . $this->document->getId(),
            $iconMethod->invokeArgs($component, [$target]),
        );

        $target = new ChatConversationTarget($this->document->getId(), $this->document->getTitle());
        self::assertSame(
            '/chat/' . $this->document->getId(),
            $iconMethod->invokeArgs($component, [$target]),
        );

        $target = new WorldItemTarget($this->document->getId(), $this->document->getTitle());
        self::assertSame(
            '/world/item/' . $this->document->getId(),
            $iconMethod->invokeArgs($component, [$target]),
        );

        $target = self::createStub(Target::class);
        self::assertSame('#', $iconMethod->invokeArgs($component, [$target]));
    }
}
