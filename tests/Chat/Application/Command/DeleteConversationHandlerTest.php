<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Command;

use ChronicleKeeper\Chat\Application\Command\DeleteConversation;
use ChronicleKeeper\Chat\Application\Command\DeleteConversationHandler;
use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Domain\Event\ConversationDeleted;
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

#[CoversClass(DeleteConversationHandler::class)]
#[CoversClass(DeleteConversation::class)]
#[Large]
class DeleteConversationHandlerTest extends DatabaseTestCase
{
    private DeleteConversationHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(DeleteConversationHandler::class);
        assert($handler instanceof DeleteConversationHandler);

        $this->handler = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handler);
    }

    #[Test]
    public function itCanSuccessfullExecuteADeletion(): void
    {
        // ------------------- The test setup -------------------
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

        $this->bus->dispatch(new StoreConversation($conversation));

        // ------------------- The test execution -------------------

        $messageResult = ($this->handler)(new DeleteConversation($conversation));

        // ------------------- The test assertions -------------------

        self::assertCount(1, $messageResult->getEvents());
        self::assertInstanceOf(ConversationDeleted::class, $messageResult->getEvents()[0]);
        self::assertSame($conversation, $messageResult->getEvents()[0]->conversation);

        $this->assertTableIsEmpty('conversations');
        $this->assertTableIsEmpty('conversation_messages');
        $this->assertTableIsEmpty('conversation_settings');
    }
}
