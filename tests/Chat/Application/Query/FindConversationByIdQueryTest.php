<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdQuery;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessageBag;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(FindConversationByIdQuery::class)]
#[CoversClass(FindConversationByIdParameters::class)]
#[UsesClass(RootDirectory::class)]
#[UsesClass(Settings::class)]
#[UsesClass(ExtendedMessageBag::class)]
#[UsesClass(Conversation::class)]
#[Small]
class FindConversationByIdQueryTest extends TestCase
{
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
            ->with('{"id":"123e4567-e89b-12d3-a456-426614174000","title":"Test conversation"}', Conversation::class, 'json')
            ->willReturn($conversation);

        $query  = new FindConversationByIdQuery($fileAccess, $serializer);
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

        $query  = new FindConversationByIdQuery($fileAccess, $serializer);
        $result = $query->query($parameters);

        self::assertNull($result);
    }
}
