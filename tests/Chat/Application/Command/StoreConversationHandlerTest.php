<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Command;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Command\StoreConversationHandler;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBagBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\AssistantMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\SystemMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\UserMessageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(StoreConversationHandler::class)]
#[CoversClass(StoreConversation::class)]
#[Large]
final class StoreConversationHandlerTest extends DatabaseTestCase
{
    private StoreConversationHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(StoreConversationHandler::class);
        assert($handler instanceof StoreConversationHandler);

        $this->handler = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handler);
    }

    #[Test]
    public function itCanStoreAConversation(): void
    {
        // Generate stub objects
        $messages = (new ExtendedMessageBagBuilder())
            ->withMessages(
                (new ExtendedMessageBuilder())->withMessage((new SystemMessageBuilder())->build())->build(),
                (new ExtendedMessageBuilder())->withMessage((new UserMessageBuilder())->build())->build(),
                (new ExtendedMessageBuilder())->withMessage((new AssistantMessageBuilder())->build())->build(),
            )
            ->build();

        $conversation = (new ConversationBuilder())
            ->withId('123e4567-e89b-12d3-a456-426614174000')
            ->withTitle('Test conversation')
            ->withMessages($messages)
            ->build();

        ($this->handler)(new StoreConversation($conversation));

        // Assert the data is in the database
        $entry = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->from('conversations')
            ->where('id', '=', '123e4567-e89b-12d3-a456-426614174000')
            ->fetchOneOrNull();

        self::assertIsArray($entry);
        self::assertSame('123e4567-e89b-12d3-a456-426614174000', $entry['id']);
        self::assertSame('Test conversation', $entry['title']);

        $this->assertRowsInTable('conversation_messages', 3);
        $this->assertRowsInTable('conversation_settings', 1);
    }

    #[Test]
    public function itCanConstructAValidCommand(): void
    {
        $conversation = (new ConversationBuilder())
            ->withId('123e4567-e89b-12d3-a456-426614174000')
            ->withTitle('Test conversation')
            ->build();

        $command = new StoreConversation($conversation);

        self::assertSame($conversation, $command->conversation);
    }
}
