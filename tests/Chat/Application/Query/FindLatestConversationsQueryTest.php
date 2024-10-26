<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ArrayIterator;
use ChronicleKeeper\Chat\Application\Query\FindLatestConversationsParameters;
use ChronicleKeeper\Chat\Application\Query\FindLatestConversationsQuery;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(FindLatestConversationsQuery::class)]
#[CoversClass(FindLatestConversationsParameters::class)]
#[Small]
class FindLatestConversationsQueryTest extends TestCase
{
    #[Test]
    public function queryReturnsConversations(): void
    {
        $fileAccessMock   = $this->createMock(FileAccess::class);
        $serializerMock   = $this->createMock(SerializerInterface::class);
        $pathRegistryMock = $this->createMock(PathRegistry::class);
        $finderMock       = $this->createMock(Finder::class);

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
    public function parameters(): void
    {
        $parameters = new FindLatestConversationsParameters(1);

        self::assertSame(1, $parameters->maxEntries);
    }
}
