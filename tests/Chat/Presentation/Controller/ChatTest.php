<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdQuery;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationQuery;
use ChronicleKeeper\Chat\Application\Service\ExtendedMessageBagToViewConverter;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Presentation\Controller\Chat;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\UserMessageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem\FileAccessDouble;
use ChronicleKeeper\Test\WebTestCase;
use Generator;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Model\Response\StreamResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

use function assert;
use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(Chat::class)]
#[CoversClass(ExtendedMessageBagToViewConverter::class)]
#[CoversClass(FindConversationByIdParameters::class)]
#[CoversClass(FindConversationByIdQuery::class)]
#[CoversClass(GetTemporaryConversationParameters::class)]
#[CoversClass(GetTemporaryConversationQuery::class)]
#[Large]
class ChatTest extends WebTestCase
{
    #[Test]
    public function itCanOpenTheChatWithoutAnyStoredConversation(): void
    {
        $this->client->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Unbekanntes Gespräch');
    }

    #[Test]
    public function itCanOpenTheChatWithStoredTemporaryConversation(): void
    {
        // Create the default conversation to test for
        $fileAccess = $this->client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $conversation = Conversation::createEmpty();
        $conversation->rename('My Testing Conversation');
        $conversation->getMessages()[] = (new ExtendedMessageBuilder())
            ->withMessage((new UserMessageBuilder())->withContent('Hello World')->build())
            ->build();

        $fileAccess->write(
            'temp',
            'conversation_temporary.json',
            json_encode($conversation, JSON_THROW_ON_ERROR),
        );

        $this->client->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('h2', 'My Testing Conversation');
        self::assertStringContainsString('Hello World', (string) $this->client->getResponse()->getContent());
    }

    #[Test]
    public function itCanOpenAnExistingConversation(): void
    {
        // Create the default conversation to test for
        $fileAccess = $this->client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $conversation = Conversation::createEmpty();
        $conversation->rename('My Testing Conversation');
        $conversation->getMessages()[] = $message = (new ExtendedMessageBuilder())
            ->withMessage((new UserMessageBuilder())->withContent('Hello World')->build())
            ->build();

        // Insert conversation using Doctrine DBAL
        $this->connection->insert('conversations', [
            'id' => $conversation->getId(),
            'title' => $conversation->getTitle(),
            'directory' => $conversation->getDirectory()->getId(),
        ]);

        $this->connection->insert('conversation_settings', [
            'conversation_id' => $conversation->getId(),
            'version' => $conversation->getSettings()->version,
            'temperature' => $conversation->getSettings()->temperature,
            'images_max_distance' => $conversation->getSettings()->imagesMaxDistance,
            'documents_max_distance' => $conversation->getSettings()->imagesMaxDistance,
        ]);

        assert($message->message instanceof UserMessage);
        assert(isset($message->message->content[0]) && $message->message->content[0] instanceof Text);

        $this->connection->insert('conversation_messages', [
            'id' => $message->id,
            'conversation_id' => $conversation->getId(),
            'role' => $message->message->getRole()->value,
            'content' => $message->message->content[0]->text,
            'context' => '{}',
            'debug' => '{}',
        ]);

        $this->client->request(Request::METHOD_GET, '/chat/' . $conversation->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'My Testing Conversation');
        self::assertStringContainsString('Hello World', (string) $this->client->getResponse()->getContent());
    }

    #[Test]
    public function itWillOpenATemporaryConversationWhenCalledIdentifierIsNotAvailable(): void
    {
        $this->client->request(Request::METHOD_GET, '/chat/455af8a7-14f6-4516-910e-3bdb460c5418');

        self::assertResponseRedirects('/');
    }

    #[Test]
    public function itWillStreamAMessageResponseCorrectlyWithEmptyResponse(): void
    {
        $this->client->request(Request::METHOD_GET, '/chat/stream/message?message=Hello&conversation=');
        self::assertTrue($this->client->getResponse()->isSuccessful());

        $response = $this->client->getInternalResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('data: {"type":"complete"', $response);
    }

    #[Test]
    public function itWillStreamAResponseFromAssistant(): void
    {
        $this->llmChainFactory->addCallResponse(
            'gpt-4o-mini',
            new StreamResponse($this->createGeneratorForStreamedResponse()),
        );

        $this->client->request(Request::METHOD_GET, '/chat/stream/message?message=Hello&conversation=');
        self::assertTrue($this->client->getResponse()->isSuccessful());

        $response = $this->client->getInternalResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('data: {"type":"chunk","chunk":"Hello World"}', $response);
        self::assertStringContainsString('data: {"type":"chunk","chunk":"Hello World Again"}', $response);
        self::assertStringContainsString('data: {"type":"complete"', $response);
    }

    private function createGeneratorForStreamedResponse(): Generator
    {
        yield 'Hello World';
        yield 'Hello World Again';
    }
}
