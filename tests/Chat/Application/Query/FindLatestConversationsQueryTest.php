<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ArrayIterator;
use ChronicleKeeper\Chat\Application\Query\FindLatestConversationsParameters;
use ChronicleKeeper\Chat\Application\Query\FindLatestConversationsQuery;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessageBag;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\SymfonyFinder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(FindLatestConversationsQuery::class)]
#[CoversClass(FindLatestConversationsParameters::class)]
#[UsesClass(Conversation::class)]
#[UsesClass(Settings::class)]
#[UsesClass(ExtendedMessageBag::class)]
#[UsesClass(RootDirectory::class)]
#[Small]
class FindLatestConversationsQueryTest extends TestCase
{
    #[Test]
    public function testQueryReturnsConversations(): void
    {
        $fileAccessMock   = $this->createMock(FileAccess::class);
        $serializerMock   = $this->createMock(SerializerInterface::class);
        $pathRegistryMock = $this->createMock(PathRegistry::class);
        $finderMock       = $this->createMock(SymfonyFinder::class);

        $pathRegistryMock->method('get')->willReturn('/some/directory');

        $fileMock = $this->createMock(SplFileInfo::class);
        $fileMock->method('getFilename')->willReturn('conversation.json');

        $finderMock->method('findFilesInDirectoryOrderedByAccessTimestamp')->willReturn(new ArrayIterator([$fileMock]));

        $conversation = (new ConversationBuilder())->build();
        $serializerMock->method('deserialize')->willReturn($conversation);

        $query = new FindLatestConversationsQuery(
            $pathRegistryMock,
            $fileAccessMock,
            $serializerMock,
            $finderMock,
        );

        $parameters = new FindLatestConversationsParameters(1);

        $result = $query->query($parameters);

        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertSame($conversation, $result[0]);
    }

    #[Test]
    public function testParameters(): void
    {
        $parameters = new FindLatestConversationsParameters(1);

        self::assertSame(1, $parameters->maxEntries);
    }
}
