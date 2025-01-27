<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Presentation\Controller;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\WebTestCase;
use ChronicleKeeper\World\Application\Query\FindAllReferencableMedia;
use ChronicleKeeper\World\Application\Query\FindAllReferencableMediaQuery;
use ChronicleKeeper\World\Presentation\Controller\WorldItemMediaAutocomplete;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(WorldItemMediaAutocomplete::class)]
#[CoversClass(FindAllReferencableMedia::class)]
#[CoversClass(FindAllReferencableMediaQuery::class)]
#[Large]
class WorldItemMediaAutocompleteTest extends WebTestCase
{
    private Document $document;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->document = (new DocumentBuilder())->withTitle('I am a test')->build();
        $this->bus->dispatch(new StoreDocument($this->document));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->document);
    }

    #[Test]
    public function itReturnsEmptyResultsForEmptyQuery(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/media/autocomplete',
            ['query' => ''],
        );

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            '{"results":[]}',
            (string) $this->client->getResponse()->getContent(),
        );
    }

    #[Test]
    public function itReturnsResultsForValidQuery(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/media/autocomplete',
            ['query' => 'Test'],
        );

        self::assertResponseIsSuccessful();
        self::assertStringContainsString(
            'I am a test',
            (string) $this->client->getResponse()->getContent(),
        );
    }

    #[Test]
    public function itReturnsEmptyResultsForNonMatchingQuery(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/world/item/media/autocomplete',
            ['query' => 'Nonexistent'],
        );

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            '{"results":[]}',
            (string) $this->client->getResponse()->getContent(),
        );
    }
}
