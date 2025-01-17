<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Presentation\Twig;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Favorizer\Presentation\Twig\AddToShortcuts;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

#[CoversClass(AddToShortcuts::class)]
#[Large]
class AddToShortcutsTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    #[Test]
    public function render(): void
    {
        $document = (new DocumentBuilder())->withId('f3ce2cce-888d-4812-8470-72cdd96faf4c')->build();

        $this->databasePlatform->insert('documents', [
            'id' => $document->getId(),
            'title' => $document->getTitle(),
            'content' => $document->getContent(),
            'directory' => $document->getDirectory()->getId(),
            'last_updated' => $document->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $component = $this->createLiveComponent(
            name: AddToShortcuts::class,
            data: ['id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c', 'type' => Document::class],
        );

        $renderedComponent = $component->render()->toString();

        // Check if rendered correct and the document is not yet favorized
        self::assertStringContainsString('Zu Favoriten hinzufügen', $renderedComponent);
    }
}
