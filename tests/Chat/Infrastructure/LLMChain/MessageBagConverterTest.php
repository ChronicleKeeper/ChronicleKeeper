<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Infrastructure\LLMChain;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\MessageBagConverter;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageBagConverter::class)]
#[Small]
class MessageBagConverterTest extends TestCase
{
    private MessageBagConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new MessageBagConverter();
    }

    protected function tearDown(): void
    {
        unset($this->converter);
    }

    #[Test]
    public function itConvertsEmptyMessageBag(): void
    {
        $chatMessageBag = new MessageBag();
        $llmMessageBag  = $this->converter->toLlmMessageBag($chatMessageBag);

        self::assertCount(0, $llmMessageBag->getMessages());
    }

    #[Test]
    public function itConvertsSystemMessage(): void
    {
        $systemMessage  = new Message('1', Role::SYSTEM, 'System prompt');
        $chatMessageBag = new MessageBag($systemMessage);

        $llmMessageBag = $this->converter->toLlmMessageBag($chatMessageBag);
        $llmMessages   = $llmMessageBag->getMessages();

        self::assertCount(1, $llmMessages);
        self::assertInstanceOf(SystemMessage::class, $llmMessages[0]);
    }

    #[Test]
    public function itConvertsUserMessage(): void
    {
        $userMessage    = new Message('1', Role::USER, 'Hello world');
        $chatMessageBag = new MessageBag($userMessage);

        $llmMessageBag = $this->converter->toLlmMessageBag($chatMessageBag);
        $llmMessages   = $llmMessageBag->getMessages();

        self::assertCount(1, $llmMessages);
        self::assertInstanceOf(UserMessage::class, $llmMessages[0]);
    }

    #[Test]
    public function itConvertsAssistantMessage(): void
    {
        $assistantMessage = new Message('1', Role::ASSISTANT, 'Assistant response');
        $chatMessageBag   = new MessageBag($assistantMessage);

        $llmMessageBag = $this->converter->toLlmMessageBag($chatMessageBag);
        $llmMessages   = $llmMessageBag->getMessages();

        self::assertCount(1, $llmMessages);
        self::assertInstanceOf(AssistantMessage::class, $llmMessages[0]);
    }

    #[Test]
    public function itConvertsMultipleMessages(): void
    {
        $systemMessage    = new Message('1', Role::SYSTEM, 'System prompt');
        $userMessage      = new Message('2', Role::USER, 'Hello world');
        $assistantMessage = new Message('3', Role::ASSISTANT, 'Assistant response');

        $chatMessageBag = new MessageBag($systemMessage, $userMessage, $assistantMessage);

        $llmMessageBag = $this->converter->toLlmMessageBag($chatMessageBag);
        $llmMessages   = $llmMessageBag->getMessages();

        self::assertCount(3, $llmMessages);

        // Check types are correct
        self::assertInstanceOf(SystemMessage::class, $llmMessages[0]);
        self::assertInstanceOf(UserMessage::class, $llmMessages[1]);
        self::assertInstanceOf(AssistantMessage::class, $llmMessages[2]);
    }

    #[Test]
    public function itPreservesMessageOrder(): void
    {
        $messages = [
            new Message('1', Role::USER, 'First'),
            new Message('2', Role::ASSISTANT, 'Second'),
            new Message('3', Role::USER, 'Third'),
        ];

        $chatMessageBag = new MessageBag(...$messages);
        $llmMessageBag  = $this->converter->toLlmMessageBag($chatMessageBag);
        $llmMessages    = $llmMessageBag->getMessages();

        self::assertCount(3, $llmMessages);

        // Check order is preserved
        self::assertInstanceOf(UserMessage::class, $llmMessages[0]);
        self::assertInstanceOf(AssistantMessage::class, $llmMessages[1]);
        self::assertInstanceOf(UserMessage::class, $llmMessages[2]);
    }

    #[Test]
    public function itCanHandleAllRoleTypes(): void
    {
        $systemMessage    = new Message('1', Role::SYSTEM, 'System');
        $userMessage      = new Message('2', Role::USER, 'User');
        $assistantMessage = new Message('3', Role::ASSISTANT, 'Assistant');

        $chatMessageBag = new MessageBag($systemMessage, $userMessage, $assistantMessage);
        $llmMessageBag  = $this->converter->toLlmMessageBag($chatMessageBag);
        $llmMessages    = $llmMessageBag->getMessages();

        self::assertCount(3, $llmMessages);

        // Verify all role types are handled
        self::assertInstanceOf(SystemMessage::class, $llmMessages[0]);
        self::assertInstanceOf(UserMessage::class, $llmMessages[1]);
        self::assertInstanceOf(AssistantMessage::class, $llmMessages[2]);
    }
}
