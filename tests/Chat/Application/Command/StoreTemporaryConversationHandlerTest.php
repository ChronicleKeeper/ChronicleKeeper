<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Command;

use ChronicleKeeper\Chat\Application\Command\StoreTemporaryConversation;
use ChronicleKeeper\Chat\Application\Command\StoreTemporaryConversationHandler;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

#[CoversClass(StoreTemporaryConversationHandler::class)]
#[CoversClass(StoreTemporaryConversation::class)]
#[Small]
class StoreTemporaryConversationHandlerTest extends TestCase
{
    #[Test]
    public function executeStore(): void
    {
        $conversation = (new ConversationBuilder())
            ->withId('123e4567-e89b-12d3-a456-426614174000')
            ->withTitle('Test conversation')
            ->build();
        $message      = new StoreTemporaryConversation($conversation);

        $fileAccess = $this->createMock(FileAccess::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $serializer->expects($this->once())
            ->method('serialize')
            ->with($conversation, 'json', ['json_encode_options' => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT])
            ->willReturn('{"id":"123e4567-e89b-12d3-a456-426614174000","title":"Test conversation"}');

        $fileAccess->expects($this->once())
            ->method('write')
            ->with(
                'temp',
                'conversation_temporary.json',
                '{"id":"123e4567-e89b-12d3-a456-426614174000","title":"Test conversation"}',
            );

        $handler = new StoreTemporaryConversationHandler($fileAccess, $serializer);
        $handler($message);
    }

    #[Test]
    public function validConversation(): void
    {
        $conversation = (new ConversationBuilder())
            ->withId('123e4567-e89b-12d3-a456-426614174000')
            ->withTitle('Test conversation')
            ->build();

        $command = new StoreTemporaryConversation($conversation);

        self::assertSame($conversation, $command->conversation);
    }
}
