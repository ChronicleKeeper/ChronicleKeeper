<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdQuery;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageDenormalizer;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotFunctions;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotGeneral;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(FindConversationByIdQuery::class)]
#[CoversClass(FindConversationByIdParameters::class)]
#[Small]
class FindConversationByIdQueryTest extends TestCase
{
    #[Test]
    public function itEnsuresTheParametersHasTheCorrectQueryClass(): void
    {
        self::assertSame(
            FindConversationByIdQuery::class,
            (new FindConversationByIdParameters('123e4567-e89b-12d3-a456-426614174000'))->getQueryClass(),
        );
    }

    #[Test]
    public function queryReturnsConversation(): void
    {
        $parameters   = new FindConversationByIdParameters('123e4567-e89b-12d3-a456-426614174000');
        $conversation = new Conversation(
            '123e4567-e89b-12d3-a456-426614174000',
            'Test conversation',
            RootDirectory::get(),
            new Settings(),
            new ExtendedMessageBag(),
        );

        $fileAccess = $this->createMock(FileAccess::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $fileAccess->expects($this->once())
            ->method('exists')
            ->with('library.conversations', '123e4567-e89b-12d3-a456-426614174000.json')
            ->willReturn(true);

        $fileAccess->expects($this->once())
            ->method('read')
            ->with('library.conversations', '123e4567-e89b-12d3-a456-426614174000.json')
            ->willReturn('{"id":"123e4567-e89b-12d3-a456-426614174000","title":"Test conversation"}');

        $serializer->expects($this->once())
            ->method('deserialize')
            ->with(
                '{"id":"123e4567-e89b-12d3-a456-426614174000","title":"Test conversation"}',
                Conversation::class,
                'json',
                [
                    ExtendedMessageDenormalizer::WITH_CONTEXT_DOCUMENTS => false,
                    ExtendedMessageDenormalizer::WITH_CONTEXT_IMAGES => false,
                    ExtendedMessageDenormalizer::WITH_DEBUG_FUNCTIONS => false,
                ],
            )
            ->willReturn($conversation);

        $query  = new FindConversationByIdQuery(
            $fileAccess,
            $serializer,
            self::createStub(SettingsHandler::class),
        );
        $result = $query->query($parameters);

        self::assertSame($conversation, $result);
    }

    #[Test]
    public function queryReturnsNullWhenConversationNotFound(): void
    {
        $parameters = new FindConversationByIdParameters('123e4567-e89b-12d3-a456-426614174000');

        $fileAccess = $this->createMock(FileAccess::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $fileAccess->expects($this->once())
            ->method('exists')
            ->with('library.conversations', '123e4567-e89b-12d3-a456-426614174000.json')
            ->willReturn(false);

        $query  = new FindConversationByIdQuery(
            $fileAccess,
            $serializer,
            self::createStub(SettingsHandler::class),
        );
        $result = $query->query($parameters);

        self::assertNull($result);
    }

    #[Test]
    public function itWillDenormalizeAConversationWithCompleteContext(): void
    {
        $parameters   = new FindConversationByIdParameters('123e4567-e89b-12d3-a456-426614174000');
        $conversation = new Conversation(
            '123e4567-e89b-12d3-a456-426614174000',
            'Test conversation',
            RootDirectory::get(),
            new Settings(),
            new ExtendedMessageBag(),
        );

        $fileAccess = $this->createMock(FileAccess::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $fileAccess->expects($this->once())
            ->method('exists')
            ->with('library.conversations', '123e4567-e89b-12d3-a456-426614174000.json')
            ->willReturn(true);

        $fileAccess->expects($this->once())
            ->method('read')
            ->with('library.conversations', '123e4567-e89b-12d3-a456-426614174000.json')
            ->willReturn('{"id":"123e4567-e89b-12d3-a456-426614174000","title":"Test conversation"}');

        $serializer->expects($this->once())
            ->method('deserialize')
            ->with(
                '{"id":"123e4567-e89b-12d3-a456-426614174000","title":"Test conversation"}',
                Conversation::class,
                'json',
                [
                    ExtendedMessageDenormalizer::WITH_CONTEXT_DOCUMENTS => true,
                    ExtendedMessageDenormalizer::WITH_CONTEXT_IMAGES => true,
                    ExtendedMessageDenormalizer::WITH_DEBUG_FUNCTIONS => true,
                ],
            )
            ->willReturn($conversation);

        $settings = (new SettingsBuilder())
            ->withChatbotGeneral(new ChatbotGeneral(showReferencedDocuments: true, showReferencedImages: true))
            ->withChatbotFunctions(new ChatbotFunctions(allowDebugOutput: true))
            ->build();

        $settingsHandler = self::createStub(SettingsHandler::class);
        $settingsHandler->method('get')->willReturn($settings);

        $query  = new FindConversationByIdQuery($fileAccess, $serializer, $settingsHandler);
        $result = $query->query($parameters);

        self::assertSame($conversation, $result);
    }
}
