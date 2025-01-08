<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Presentation\Controller;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Presentation\Controller\DocumentEdit;
use ChronicleKeeper\Document\Presentation\Form\DocumentType;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Presentation\Twig\DirectoryBreadcrumb;
use ChronicleKeeper\Library\Presentation\Twig\DirectorySelection;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\LLMChain\LLMChainFactoryDouble;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

use function assert;
use function reset;

#[CoversClass(DocumentEdit::class)]
#[CoversClass(DocumentType::class)]
#[CoversClass(DirectorySelection::class)]
#[CoversClass(DirectoryBreadcrumb::class)]
#[Large]
class DocumentEditTest extends WebTestCase
{
    private Document $fixtureDocument;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureDocument = (new DocumentBuilder())->build();
        $this->databasePlatform->insert('documents', [
            'id'    => $this->fixtureDocument->getId(),
            'title' => $this->fixtureDocument->getTitle(),
            'content' => $this->fixtureDocument->getContent(),
            'directory' => $this->fixtureDocument->getDirectory()->getId(),
            'last_updated' => $this->fixtureDocument->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
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
            '/library/document/' . Uuid::v4()->toString() . '/edit',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function itIsCompletelyLoadingFromScratch(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/library/document/' . $this->fixtureDocument->getId() . '/edit',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h2', 'Dokument "Default Title" bearbeiten');
        self::assertSelectorTextSame('ol.breadcrumb', 'Hauptverzeichnis Default Title');

        self::assertFormValue('form', 'document[title]', 'Default Title');
        self::assertFormValue('form', 'document[directory]', RootDirectory::ID);
        self::assertFormValue('form', 'document[content]', 'Default Content');
    }

    #[Test]
    public function isIsEditingADocument(): void
    {
        $llmChainFactory = $this->client->getContainer()->get(LLMChainFactory::class);
        assert($llmChainFactory instanceof LLMChainFactoryDouble);

        $llmChainFactory->addPlatformResponse(
            Embeddings::class,
            new class implements ResponseInterface {
                /** @return Vector[] */
                public function getContent(): array
                {
                    return [new Vector([1.0, 2.0, 3.0])];
                }
            },
        );

        $this->client->request(
            Request::METHOD_POST,
            '/library/document/' . $this->fixtureDocument->getId() . '/edit',
            [
                'document' => [
                    'title' => 'Test Edited Title',
                    'content' => 'Test Edited Content',
                    'directory' => RootDirectory::ID,
                ],
            ],
        );

        self::assertResponseRedirects('/library');

        // Check the new document is stored
        $documents = $this->databasePlatform->fetch('SELECT * FROM documents');
        self::assertCount(1, $documents);

        $document = reset($documents);
        self::assertStringContainsString('Test Edited Title', $document['title']);
        self::assertStringContainsString('Test Edited Content', $document['content']);
    }
}
