<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Presentation\Controller;

use ChronicleKeeper\Document\Presentation\Controller\DocumentCreation;
use ChronicleKeeper\Document\Presentation\Form\DocumentType;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Presentation\Twig\DirectoryBreadcrumb;
use ChronicleKeeper\Library\Presentation\Twig\DirectorySelection;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBagBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\AssistantMessageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem\FileAccessDouble;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function assert;
use function json_encode;
use function reset;

#[CoversClass(DocumentCreation::class)]
#[CoversClass(DocumentType::class)]
#[CoversClass(DirectorySelection::class)]
#[CoversClass(DirectoryBreadcrumb::class)]
#[Large]
class DocumentCreationTest extends WebTestCase
{
    #[Test]
    public function itIsCompletelyLoadingFromScratch(): void
    {
        $client = static::createClient();
        $client->request(
            Request::METHOD_GET,
            '/library/directory/' . RootDirectory::ID . '/create_document',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h2', 'Neues Dokument');
        self::assertSelectorTextSame('ol.breadcrumb', 'Hauptverzeichnis Neues Dokument');

        self::assertFormValue('form', 'document[title]', '');
        self::assertFormValue('form', 'document[directory]', RootDirectory::ID);
        self::assertFormValue('form', 'document[content]', '');
    }

    #[Test]
    public function itIsCreatingADocument(): void
    {
        $client = static::createClient();
        $client->request(
            Request::METHOD_POST,
            '/library/directory/' . RootDirectory::ID . '/create_document',
            [
                'document' => [
                    'title' => 'Test Title',
                    'content' => 'Test Content',
                    'directory' => RootDirectory::ID,
                ],
            ],
        );

        self::assertResponseRedirects('/library');

        // Check the new document is stored
        $fileAccess = $client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $files = $fileAccess->allOfType('library.documents');
        self::assertCount(1, $files);

        $document = reset($files);
        self::assertStringContainsString('Test Title', $document);
        self::assertStringContainsString('Test Content', $document);
    }

    #[Test]
    public function itIsNotCreatingADocumentWithInvalidData(): void
    {
        $client = static::createClient();
        $client->request(
            Request::METHOD_POST,
            '/library/directory/' . RootDirectory::ID . '/create_document',
            [
                'document' => [
                    'title' => '',
                    'content' => '',
                    'directory' => RootDirectory::ID,
                ],
            ],
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h2', 'Neues Dokument');
        self::assertSelectorTextSame('div.invalid-feedback', 'This value should not be blank.');
    }

    #[Test]
    public function itIsCreatingADocumentFromChatMessage(): void
    {
        $client = static::createClient();

        // Setup Fixtures
        $conversation = (new ConversationBuilder())
            ->withId('8e316807-592f-4e11-b298-259858dc2a2a')
            ->withTitle('Conversation Title')
            ->withMessages(
                (new ExtendedMessageBagBuilder())->withMessages(
                    (new ExtendedMessageBuilder())
                        ->withId('40a477e7-8e0a-4158-9dac-8dd9b1df9f87')
                        ->withMessage((new AssistantMessageBuilder())->withContent('Message Content')->build())
                        ->build(),
                )->build(),
            )
            ->build();

        $fileAccess = $client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $fileAccess->write(
            'library.conversations',
            '8e316807-592f-4e11-b298-259858dc2a2a.json',
            (string) json_encode($conversation),
        );

        $client->request(
            Request::METHOD_GET,
            '/library/directory/' . RootDirectory::ID . '/create_document',
            [
                'conversation' => '8e316807-592f-4e11-b298-259858dc2a2a',
                'conversation_message' => '40a477e7-8e0a-4158-9dac-8dd9b1df9f87',
            ],
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h2', 'Neues Dokument');

        self::assertFormValue('form', 'document[title]', 'Conversation Title');
        self::assertFormValue('form', 'document[directory]', RootDirectory::ID);
        self::assertFormValue('form', 'document[content]', 'Message Content');
    }

    #[Test]
    public function itIsCreatingADocumentFromTemporaryChatMessage(): void
    {
        $client = static::createClient();

        // Setup Fixtures
        $conversation = (new ConversationBuilder())
            ->withTitle('Unnamed Conversation')
            ->withMessages(
                (new ExtendedMessageBagBuilder())->withMessages(
                    (new ExtendedMessageBuilder())
                        ->withId('40a477e7-8e0a-4158-9dac-8dd9b1df9f87')
                        ->withMessage((new AssistantMessageBuilder())->withContent('Message Content')->build())
                        ->build(),
                )->build(),
            )
            ->build();

        $fileAccess = $client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $fileAccess->write(
            'temp',
            'conversation_temporary.json',
            (string) json_encode($conversation),
        );

        $client->request(
            Request::METHOD_GET,
            '/library/directory/' . RootDirectory::ID . '/create_document',
            [
                'conversation' => '8e316807-592f-4e11-b298-259858dc2a2a',
                'conversation_message' => '40a477e7-8e0a-4158-9dac-8dd9b1df9f87',
            ],
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h2', 'Neues Dokument');

        self::assertFormValue('form', 'document[title]', 'Unnamed Conversation');
        self::assertFormValue('form', 'document[directory]', RootDirectory::ID);
        self::assertFormValue('form', 'document[content]', 'Message Content');
    }
}
