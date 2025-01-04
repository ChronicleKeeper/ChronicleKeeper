<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Command;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Command\StoreConversationHandler;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoreConversationHandler::class)]
#[CoversClass(StoreConversation::class)]
#[Small]
class StoreConversationHandlerTest extends TestCase
{
    #[Test]
    public function itCanStoreAConversation(): void
    {
        $conversation = (new ConversationBuilder())
            ->withId('123e4567-e89b-12d3-a456-426614174000')
            ->withTitle('Test conversation')
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

        $databasePlatform->assertExecutedInsertsCount(2);
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
