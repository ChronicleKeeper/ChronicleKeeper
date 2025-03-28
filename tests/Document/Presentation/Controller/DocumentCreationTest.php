<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Presentation\Controller;

use ChronicleKeeper\Document\Presentation\Controller\DocumentCreation;
use ChronicleKeeper\Document\Presentation\Form\DocumentType;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Presentation\Twig\DirectoryBreadcrumb;
use ChronicleKeeper\Library\Presentation\Twig\DirectorySelection;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBagBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\AssistantMessageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\LLMChain\LLMChainFactoryDouble;
use ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem\FileAccessDouble;
use ChronicleKeeper\Test\WebTestCase;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

use function array_map;
use function assert;
use function json_encode;
use function mt_getrandmax;
use function mt_rand;
use function range;
use function reset;

use const JSON_THROW_ON_ERROR;

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
        $this->client->request(
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
        $llmChainFactory = $this->client->getContainer()->get(LLMChainFactory::class);
        assert($llmChainFactory instanceof LLMChainFactoryDouble);

        $llmChainFactory->addPlatformResponse(
            Embeddings::class,
            new class implements ResponseInterface {
                /** @return Vector[] */
                public function getContent(): array
                {
                    return [new Vector(array_map(static fn () => mt_rand() / mt_getrandmax(), range(1, 1536)))];
                }
            },
        );

        $this->client->request(
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
        $documents = $this->databasePlatform->fetch('SELECT * FROM documents');
        self::assertCount(1, $documents);

        $document = reset($documents);
        self::assertStringContainsString('Test Title', $document['title']);
        self::assertStringContainsString('Test Content', $document['content']);
    }

    #[Test]
    public function itIsNotCreatingADocumentWithInvalidData(): void
    {
        $this->client->request(
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
        // Setup Fixtures
        $conversation        = (new ConversationBuilder())
            ->withId('8e316807-592f-4e11-b298-259858dc2a2a')
            ->withTitle('Conversation Title')
            ->withMessages(
                (new ExtendedMessageBagBuilder())->withMessages(
                    $message = (new ExtendedMessageBuilder())
                        ->withId('40a477e7-8e0a-4158-9dac-8dd9b1df9f87')
                        ->withMessage((new AssistantMessageBuilder())->withContent('Message Content')->build())
                        ->build(),
                )->build(),
            )
            ->build();

        $fileAccess = $this->client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $this->databasePlatform->createQueryBuilder()->createInsert()
            ->insert('conversations')
            ->values([
                'id' => $conversation->getId(),
                'title' => $conversation->getTitle(),
                'directory' => $conversation->getDirectory()->getId(),
            ])
            ->execute();

        $this->databasePlatform->createQueryBuilder()->createInsert()
            ->insert('conversation_settings')
            ->values([
                'conversation_id' => $conversation->getId(),
                'version' => $conversation->getSettings()->version,
                'temperature' => $conversation->getSettings()->temperature,
                'images_max_distance' => $conversation->getSettings()->imagesMaxDistance,
                'documents_max_distance' => $conversation->getSettings()->imagesMaxDistance,
            ])
            ->execute();

        assert($message->message instanceof AssistantMessage);

        $this->databasePlatform->createQueryBuilder()->createInsert()
            ->insert('conversation_messages')
            ->values([
                'id' => $message->id,
                'conversation_id' => $conversation->getId(),
                'role' => $message->message->getRole()->value,
                'content' => $message->message->content,
                'context' => '{}',
                'debug' => '{}',
            ])
            ->execute();

        $this->client->request(
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

        $fileAccess = $this->client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $fileAccess->write(
            'temp',
            'conversation_temporary.json',
            json_encode($conversation, JSON_THROW_ON_ERROR),
        );

        $this->client->request(
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
