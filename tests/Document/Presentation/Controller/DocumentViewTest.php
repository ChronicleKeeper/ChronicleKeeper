<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Presentation\Controller;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Presentation\Controller\DocumentView;
use ChronicleKeeper\Library\Presentation\Twig\DirectoryBreadcrumb;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

#[CoversClass(DocumentView::class)]
#[CoversClass(DirectoryBreadcrumb::class)]
#[Large]
class DocumentViewTest extends WebTestCase
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
        $this->client->request(
            Request::METHOD_GET,
            '/library/document/' . Uuid::v4()->toString(),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function itIsCompletelyLoadingFromScratch(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/library/document/' . $this->fixtureDocument->getId(),
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h2', 'Default Title');
        self::assertSelectorTextSame('ol.breadcrumb', 'Hauptverzeichnis Default Title');
        self::assertSelectorTextSame('div.markdown', 'Default Content');
    }
}
