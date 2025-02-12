<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Presentation\Controller\Chat;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\UserMessageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem\FileAccessDouble;
use ChronicleKeeper\Test\WebTestCase;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

use function assert;
use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(Chat::class)]
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

        assert($message->message instanceof UserMessage);
        assert(isset($message->message->content[0]) && $message->message->content[0] instanceof Text);

        $this->databasePlatform->createQueryBuilder()->createInsert()
            ->insert('conversation_messages')
            ->values([
                'id' => $message->id,
                'conversation_id' => $conversation->getId(),
                'role' => $message->message->getRole()->value,
                'content' => $message->message->content[0]->text,
                'context' => '{}',
                'debug' => '{}',
            ])
            ->execute();

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
}
