<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Presentation\Controller;

use ChronicleKeeper\Document\Presentation\Controller\DocumentUpload;
use ChronicleKeeper\Document\Presentation\Form\DocumentUploadType;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Presentation\Twig\DirectoryBreadcrumb;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Shared\Infrastructure\LLMChain\LLMChainFactoryDouble;
use ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem\FileAccessDouble;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

use function assert;
use function json_decode;
use function reset;

#[CoversClass(DocumentUpload::class)]
#[CoversClass(DocumentUploadType::class)]
#[CoversClass(DirectoryBreadcrumb::class)]
#[Large]
class DocumentUploadTest extends WebTestCase
{
    #[Test]
    public function itIsCompletelyLoadingFromScratch(): void
    {
        $client = static::createClient();
        $client->request(
            Request::METHOD_GET,
            '/library/directory/' . RootDirectory::ID . '/upload_document',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h2', 'Dokument Hochladen');
        self::assertSelectorTextSame('ol.breadcrumb', 'Hauptverzeichnis Dokument Hochladen');

        self::assertSelectorExists('#document_upload_document');
        self::assertCheckboxChecked('document_upload[optimize]');
    }

    #[Test]
    public function itCanUploadADocumentWithoutOptimization(): void
    {
        $uploadedFile = new UploadedFile(
            __DIR__ . '/Stubs/hello_world.txt',
            'hello_world.txt',
            'text/plain',
            0,
            true,
        );

        $client = static::createClient();

        $llmChainFactory = $client->getContainer()->get(LLMChainFactory::class);
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

        $client->request(
            Request::METHOD_POST,
            '/library/directory/' . RootDirectory::ID . '/upload_document',
            parameters: [], // Do not mention the optimization checkbox to let it be false
            files: ['document_upload' => ['document' => $uploadedFile]],
            server: ['CONTENT_TYPE' => 'multipart/form-data'],
        );

        // Check the new document is stored
        $fileAccess = $client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $files = $fileAccess->allOfType('library.documents');
        self::assertCount(1, $files);

        $document = reset($files);
        $document = json_decode($document, associative: true);

        self::assertResponseRedirects('/library/document/' . $document['id']);
    }
}
