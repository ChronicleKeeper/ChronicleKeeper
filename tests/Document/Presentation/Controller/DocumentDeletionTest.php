<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Presentation\Controller;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Presentation\Controller\DocumentDeletion;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

#[CoversClass(DocumentDeletion::class)]
#[Large]
class DocumentDeletionTest extends WebTestCase
{
    private Document $fixtureDocument;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureDocument = (new DocumentBuilder())->build();

        $this->databasePlatform->createQueryBuilder()->createInsert()
            ->insert('documents')
            ->values([
                'id'          => $this->fixtureDocument->getId(),
                'title'       => $this->fixtureDocument->getTitle(),
                'content'     => $this->fixtureDocument->getContent(),
                'directory'   => $this->fixtureDocument->getDirectory()->getId(),
                'last_updated' => $this->fixtureDocument->getUpdatedAt()->format('Y-m-d H:i:s'),
            ])
            ->execute();
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->fixtureDocument);
    }

    #[Test]
    public function itWillResponseWithNotFoundForUnknownDocument(): void
    {
        $documentId = Uuid::v4()->toString();

        $this->client->request(
            Request::METHOD_GET,
            '/library/document/' . $documentId . '/delete',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function itWillRedirectToLibraryIfNoConfirmation(): void
    {
        // Execute deletion without confirmation
        $this->client->request(
            Request::METHOD_GET,
            '/library/document/' . $this->fixtureDocument->getId() . '/delete',
        );

        self::assertResponseRedirects('/library');

        // Get the document from database
        $document = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->select('*')
            ->from('documents')
            ->where('id', '=', $this->fixtureDocument->getId())
            ->fetchOneOrNull();

        self::assertNotNull($document);
    }

    #[Test]
    public function itWillRedirectToLibraryAfterDeletion(): void
    {
        // Execute deletion without confirmation
        $this->client->request(
            Request::METHOD_GET,
            '/library/document/' . $this->fixtureDocument->getId() . '/delete?confirm=1',
        );

        self::assertResponseRedirects('/library');

        // Get the document from database
        $document = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->select('*')
            ->from('documents')
            ->where('id', '=', $this->fixtureDocument->getId())
            ->fetchOneOrNull();

        self::assertNull($document);
    }
}
