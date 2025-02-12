<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Presentation\Controller\Image;

use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Library\Presentation\Controller\Image\ImageDownload;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(ImageDownload::class)]
#[Large]
final class ImageDownloadTest extends WebTestCase
{
    #[Test]
    public function isIsRedirectingToImageViewWhenEmbeddedImageIsInvalid(): void
    {
        // -------------- Setup Fixtures --------------

        $image = (new ImageBuilder())->withEncodedImage('\\\\')->build();
        $this->bus->dispatch(new StoreImage($image));

        // -------------- Execute Test --------------

        $this->client->request(
            Request::METHOD_GET,
            '/library/image/' . $image->getId() . '/download',
        );

        // -------------- Asserts --------------

        self::assertResponseRedirects('/library/image/' . $image->getId());
    }

    #[Test]
    public function itIsDownloadingAnImage(): void
    {
        // -------------- Setup Fixtures --------------

        $image = (new ImageBuilder())->build();
        $this->bus->dispatch(new StoreImage($image));

        // -------------- Execute Test --------------

        $this->client->request(
            Request::METHOD_GET,
            '/library/image/' . $image->getId() . '/download',
        );

        // -------------- Asserts --------------

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'image/png');
        self::assertResponseHeaderSame(
            'Content-Disposition',
            'attachment;filename="' . $image->getTitle() . '"',
        );
        self::assertResponseHeaderSame('Content-Length', (string) $image->getSize());
    }
}
