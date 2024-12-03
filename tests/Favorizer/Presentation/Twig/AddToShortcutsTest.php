<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Presentation\Twig;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Favorizer\Presentation\Twig\AddToShortcuts;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Library\Domain\Entity\DocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

use function assert;
use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(AddToShortcuts::class)]
#[Large]
class AddToShortcutsTest extends KernelTestCase
{
    use InteractsWithLiveComponents;

    #[Test]
    public function render(): void
    {
        $document     = (new DocumentBuilder())->build();
        $document->id = 'f3ce2cce-888d-4812-8470-72cdd96faf4c';

        $fileAccess = self::getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccess);
        $fileAccess->write(
            'library.documents',
            'f3ce2cce-888d-4812-8470-72cdd96faf4c.json',
            json_encode($document, JSON_THROW_ON_ERROR),
        );

        $component = $this->createLiveComponent(
            name: AddToShortcuts::class,
            data: ['id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c', 'type' => Document::class],
        );

        $renderedComponent = $component->render()->toString();

        // Check if rendered correct and the document is not yet favorized
        self::assertStringContainsString('Zu Favoriten hinzufügen', $renderedComponent);
    }
}
