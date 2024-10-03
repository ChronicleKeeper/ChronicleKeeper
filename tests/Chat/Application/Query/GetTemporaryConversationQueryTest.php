<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ChronicleKeeper\Chat\Application\Command\StoreTemporaryConversation;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationQuery;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings as ChatSettings;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessage;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessageBag;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(GetTemporaryConversationQuery::class)]
#[CoversClass(GetTemporaryConversationParameters::class)]
#[UsesClass(UnableToReadFile::class)]
#[UsesClass(RootDirectory::class)]
#[UsesClass(Conversation::class)]
#[UsesClass(Settings::class)]
#[UsesClass(ExtendedMessageBag::class)]
#[UsesClass(ExtendedMessage::class)]
#[UsesClass(ChatSettings::class)]
#[UsesClass(StoreTemporaryConversation::class)]
#[Small]
class GetTemporaryConversationQueryTest extends TestCase
{
    #[Test]
    public function testQueryReturnsConversation(): void
    {
        $fileAccessMock      = $this->createMock(FileAccess::class);
        $serializerMock      = $this->createMock(SerializerInterface::class);
        $busMock             = $this->createMock(MessageBusInterface::class);
        $settingsHandlerMock = $this->createMock(SettingsHandler::class);

        $conversation = (new ConversationBuilder())->build();
        $fileAccessMock->method('read')->willReturn('{"id": "550e8400-e29b-41d4-a716-446655440000"}');
        $serializerMock->method('deserialize')->willReturn($conversation);

        $query = new GetTemporaryConversationQuery(
            $fileAccessMock,
            $serializerMock,
            $busMock,
            $settingsHandlerMock,
        );

        $parameters = new GetTemporaryConversationParameters();

        $result = $query->query($parameters);

        self::assertInstanceOf(Conversation::class, $result);
        self::assertSame($conversation, $result);
    }

    #[Test]
    public function testQueryHandlesFileNotFound(): void
    {
        $fileAccessMock      = $this->createMock(FileAccess::class);
        $serializerMock      = $this->createMock(SerializerInterface::class);
        $busMock             = $this->createMock(MessageBusInterface::class);
        $settingsHandlerMock = $this->createMock(SettingsHandler::class);

        $fileAccessMock->method('read')->willThrowException(new UnableToReadFile('foo'));

        $settingsHandlerMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(self::createStub(Settings::class));

        $busMock->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(StoreTemporaryConversation::class))
            ->willReturn(new Envelope(new stdClass()));

        $query = new GetTemporaryConversationQuery(
            $fileAccessMock,
            $serializerMock,
            $busMock,
            $settingsHandlerMock,
        );

        $result = $query->query(new GetTemporaryConversationParameters());

        self::assertInstanceOf(Conversation::class, $result);
    }

    #[Test]
    public function testParameters(): void
    {
        $parameters = new GetTemporaryConversationParameters();

        self::assertInstanceOf(GetTemporaryConversationParameters::class, $parameters);
    }
}
