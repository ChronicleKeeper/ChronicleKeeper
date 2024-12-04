<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Presentation\Controller;

use ChronicleKeeper\Document\Presentation\Controller\DocumentDeletion;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem\FileAccessDouble;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

use function assert;
use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(DocumentDeletion::class)]
#[Large]
class DocumentDeletionTest extends WebTestCase
{
    #[Test]
    public function itWillResponseWithNotFoundForUnknownDocument(): void
    {
        $documentId = Uuid::v4()->toString();

        $client = static::createClient();
        $client->request(
            Request::METHOD_GET,
            '/library/document/' . $documentId . '/delete',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function itWillRedirectToLibraryIfNoConfirmation(): void
    {
        $document = (new DocumentBuilder())->build();
        $client   = static::createClient();

        // Initialize a fixture to delete
        $fileAccess = $client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $fileAccess->write(
            'library.documents',
            $document->id . '.json',
            json_encode($document, JSON_THROW_ON_ERROR),
        );

        // Execute deletion without confirmation
        $client->request(
            Request::METHOD_GET,
            '/library/document/' . $document->id . '/delete',
        );

        self::assertResponseRedirects('/library');

        // Check that file still exists
        self::assertTrue($fileAccess->exists('library.documents', $document->id . '.json'));
    }

    #[Test]
    public function itWillRedirectToLibraryAfterDeletion(): void
    {
        $document = (new DocumentBuilder())->build();
        $client   = static::createClient();

        // Initialize a fixture to delete
        $fileAccess = $client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $fileAccess->write(
            'library.documents',
            $document->id . '.json',
            json_encode($document, JSON_THROW_ON_ERROR),
        );

        // Execute deletion without confirmation
        $client->request(
            Request::METHOD_GET,
            '/library/document/' . $document->id . '/delete?confirm=1',
        );

        self::assertResponseRedirects('/library');

        // Check that file is removed
        self::assertFalse($fileAccess->exists('library.documents', $document->id . '.json'));
    }
}
