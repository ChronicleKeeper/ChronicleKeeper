<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Presentation\Controller\Chat;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\UserMessageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem\FileAccessDouble;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
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
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Unbekanntes Gespräch');
    }

    #[Test]
    public function itCanOpenTheChatWithStoredTemporaryConversation(): void
    {
        $client = static::createClient();

        // Create the default conversation to test for
        $fileAccess = $client->getContainer()->get(FileAccess::class);
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

        $client->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('h2', 'My Testing Conversation');
        self::assertStringContainsString('Hello World', (string) $client->getResponse()->getContent());
    }

    #[Test]
    public function itCanOpenAnExistingConversation(): void
    {
        $client = static::createClient();

        // Create the default conversation to test for
        $fileAccess = $client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $conversation = Conversation::createEmpty();
        $conversation->rename('My Testing Conversation');
        $conversation->getMessages()[] = (new ExtendedMessageBuilder())
            ->withMessage((new UserMessageBuilder())->withContent('Hello World')->build())
            ->build();

        $fileAccess->write(
            'library.conversations',
            $conversation->getId() . '.json',
            json_encode($conversation, JSON_THROW_ON_ERROR),
        );

        $client->request(Request::METHOD_GET, '/chat/' . $conversation->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'My Testing Conversation');
        self::assertStringContainsString('Hello World', (string) $client->getResponse()->getContent());
    }

    #[Test]
    public function itWillOpenATemporaryConversationWhenCalledIdentifierIsNotAvailable(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/chat/455af8a7-14f6-4516-910e-3bdb460c5418');

        self::assertResponseRedirects('/');
    }
}
