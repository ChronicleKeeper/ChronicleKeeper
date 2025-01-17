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
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(StoreConversationHandler::class)]
#[CoversClass(StoreConversation::class)]
#[Small]
class StoreConversationHandlerTest extends TestCase
{
    #[Test]
    public function itCanStoreAConversation(): void
    {
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

        $databasePlatform = new DatabasePlatformMock();
        $handler          = new StoreConversationHandler($databasePlatform);
        $handler(new StoreConversation($conversation));

        $databasePlatform->assertExecutedInsert('conversations', [
            'id' => $conversation->getId(),
            'title' => $conversation->getTitle(),
            'directory' => $conversation->getDirectory()->getId(),
        ]);

        $databasePlatform->assertExecutedInsert('conversation_settings', [
            'conversation_id' => $conversation->getId(),
            'version' => $conversation->getSettings()->version,
            'temperature' => $conversation->getSettings()->temperature,
            'images_max_distance' => $conversation->getSettings()->imagesMaxDistance,
            'documents_max_distance' => $conversation->getSettings()->documentsMaxDistance,
        ]);

        $databasePlatform->assertExecutedQuery(
            'DELETE FROM conversation_messages WHERE conversation_id = :conversation_id',
            ['conversation_id' => $conversation->getId()],
        );

        $databasePlatform->assertExecutedQuery('BEGIN TRANSACTION');
        $databasePlatform->assertExecutedQuery('COMMIT');
        $databasePlatform->assertExecutedInsertsCount(5);
    }

    #[Test]
    public function testItWillMakeARollbackOnExceptionDuringSaving(): void
    {
        $conversation = (new ConversationBuilder())
            ->withId('123e4567-e89b-12d3-a456-426614174000')
            ->withTitle('Test conversation')
            ->build();

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->throwExceptionOnInsertToTable(
            'conversations',
            new RuntimeException('Test exception'),
        );

        try {
            $handler = new StoreConversationHandler($databasePlatform);
            $handler(new StoreConversation($conversation));
        } catch (RuntimeException $exception) {
            self::assertSame('Test exception', $exception->getMessage());
        }

        $databasePlatform->assertExecutedQuery('ROLLBACK');
    }

    #[Test]
    public function validConversation(): void
    {
        $conversation = (new ConversationBuilder())
            ->withId('123e4567-e89b-12d3-a456-426614174000')
            ->withTitle('Test conversation')
            ->build();

        $command = new StoreConversation($conversation);

        self::assertSame($conversation, $command->conversation);
    }
}
