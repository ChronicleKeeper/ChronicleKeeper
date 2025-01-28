<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Presentation\Twig;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Favorizer\Application\Query\GetTargetBag;
use ChronicleKeeper\Favorizer\Application\Query\GetTargetBagQuery;
use ChronicleKeeper\Favorizer\Presentation\Twig\AddToShortcuts;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

#[CoversClass(AddToShortcuts::class)]
#[CoversClass(GetTargetBag::class)]
#[CoversClass(GetTargetBagQuery::class)]
#[Large]
class AddToShortcutsTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    private Document $document;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->document = (new DocumentBuilder())->withId('f3ce2cce-888d-4812-8470-72cdd96faf4c')->build();
        $this->bus->dispatch(new StoreDocument($this->document));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->document);
    }

    #[Test]
    public function itIsRenderedCorrectly(): void
    {
        $component = $this->createLiveComponent(
            name: AddToShortcuts::class,
            data: ['id' => $this->document->getId(), 'type' => Document::class],
        );

        $renderedComponent = $component->render()->toString();

        // Check if rendered correct and the document is not yet favorized
        self::assertStringContainsString('Zu Favoriten hinzufügen', $renderedComponent);
    }

    #[Test]
    public function itCanToggleFavoriteStatus(): void
    {
        $component = $this->createLiveComponent(
            name: AddToShortcuts::class,
            data: ['id' => $this->document->getId(), 'type' => Document::class],
        );

        $renderedComponent = $component->render()->toString();
        self::assertStringContainsString('Zu Favoriten hinzufügen', $renderedComponent);

        // Add to favorites
        $component->call('favorize');
        $renderedComponent = $component->render()->toString();
        self::assertStringContainsString('Von Favoriten entfernen', $renderedComponent);

        // Remove from favorites
        $component->call('favorize');
        $renderedComponent = $component->render()->toString();
        self::assertStringContainsString('Zu Favoriten hinzufügen', $renderedComponent);
    }

    #[Test]
    public function itHandlesFavoritesUpdatedEvent(): void
    {
        $component = $this->createLiveComponent(
            name: AddToShortcuts::class,
            data: ['id' => $this->document->getId(), 'type' => Document::class],
        );

        $component->emit('favorites_updated');

        $renderedComponent = $component->render()->toString();
        self::assertNotEmpty($renderedComponent);
    }

    #[Test]
    public function itRendersAsButtonWhenConfigured(): void
    {
        $component = $this->createLiveComponent(
            name: AddToShortcuts::class,
            data: [
                'id' => $this->document->getId(),
                'type' => Document::class,
                'asButton' => true,
            ],
        );

        $renderedComponent = $component->render()->toString();
        self::assertStringContainsString('btn btn-icon', $renderedComponent);
    }
}
