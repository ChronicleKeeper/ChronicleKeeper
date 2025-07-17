<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Command;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Command\StoreConversationHandler;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\AssistantMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\SystemMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\UserMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\MessageBagBuilder;
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

        unset($this->handler, $this->connection);
    }

    #[Test]
    public function itCanStoreAConversation(): void
    {
        // Generate stub objects
        $messages = (new MessageBagBuilder())
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

        // Assert the data is in the database using Doctrine DBAL
        $queryBuilder = $this->connection->createQueryBuilder();
        $result       = $queryBuilder
            ->select('*')
            ->from('conversations')
            ->where('id = :id')
            ->setParameter('id', '123e4567-e89b-12d3-a456-426614174000')
            ->executeQuery()
            ->fetchAssociative();

        self::assertIsArray($result);
        self::assertSame('123e4567-e89b-12d3-a456-426614174000', $result['id']);
        self::assertSame('Test conversation', $result['title']);

        $this->assertMessageCount(3);
        $this->assertSettingsCount(1);
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

    /**
     * Assert the number of rows in the conversation_messages table
     */
    private function assertMessageCount(int $expectedCount): void
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $count        = (int) $queryBuilder
            ->select('COUNT(*)')
            ->from('conversation_messages')
            ->executeQuery()
            ->fetchOne();

        self::assertSame($expectedCount, $count);
    }

    /**
     * Assert the number of rows in the conversation_settings table
     */
    private function assertSettingsCount(int $expectedCount): void
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $count        = (int) $queryBuilder
            ->select('COUNT(*)')
            ->from('conversation_settings')
            ->executeQuery()
            ->fetchOne();

        self::assertSame($expectedCount, $count);
    }
}
