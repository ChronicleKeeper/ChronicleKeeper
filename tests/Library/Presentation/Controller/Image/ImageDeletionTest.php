<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Presentation\Controller\Image;

use ChronicleKeeper\Image\Application\Command\DeleteImage;
use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Library\Presentation\Controller\Image\ImageDeletion;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(ImageDeletion::class)]
#[CoversClass(DeleteImage::class)]
#[Large]
final class ImageDeletionTest extends WebTestCase
{
    #[Test]
    public function itReturnsToLibraryAndShowsMessageWhenNotConfirmed(): void
    {
        // -------------- Setup Data Fixtures --------------

        $image = (new ImageBuilder())->build();
        $this->bus->dispatch(new StoreImage($image));

        // -------------- Execute Test --------------

        $this->client->request(
            Request::METHOD_POST,
            '/library/image/' . $image->getId() . '/delete',
        );

        // -------------- Asserts --------------

        self::assertResponseRedirects('/library');
        $this->client->followRedirect();

        self::assertSelectorTextContains('h2', 'Bibliothek');
        self::assertSelectorTextContains(
            'div.alert-warning',
            'Das Löschen des Bildes "Default Title" muss erst bestätigt werden!',
        );
    }

    #[Test]
    public function itDeletesAnImage(): void
    {
        // -------------- Setup Data Fixtures --------------

        $image = (new ImageBuilder())->build();
        $this->bus->dispatch(new StoreImage($image));

        // -------------- Execute Test --------------

        $this->client->request(
            Request::METHOD_POST,
            '/library/image/' . $image->getId() . '/delete',
            ['confirm' => 1],
        );

        // -------------- Asserts --------------

        self::assertResponseRedirects('/library');
        $this->client->followRedirect();

        self::assertSelectorTextContains('h2', 'Bibliothek');
        self::assertSelectorTextContains(
            'div.alert-success',
            'Das Bild "Default Title" wurde erfolgreich gelöscht.',
        );

        self::assertNull(
            $this->databasePlatform
                ->createQueryBuilder()
                ->createSelect()
                ->from('images')
                ->where('id', '=', $image->getId())
                ->fetchOneOrNull(),
        );
    }
}
