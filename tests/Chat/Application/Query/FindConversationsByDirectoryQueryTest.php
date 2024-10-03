<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ArrayIterator;
use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryQuery;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\Finder;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(FindConversationsByDirectoryQuery::class)]
#[CoversClass(FindConversationsByDirectoryParameters::class)]
#[Small]
class FindConversationsByDirectoryQueryTest extends TestCase
{
    #[Test]
    public function queryReturnsConversations(): void
    {
        $fileAccessMock   = $this->createMock(FileAccess::class);
        $serializerMock   = $this->createMock(SerializerInterface::class);
        $loggerMock       = $this->createMock(LoggerInterface::class);
        $pathRegistryMock = $this->createMock(PathRegistry::class);
        $finderMock       = $this->createMock(Finder::class);

        $pathRegistryMock->method('get')->willReturn('/some/directory');

        $fileMock = $this->createMock(SplFileInfo::class);
        $fileMock->method('getFilename')->willReturn('conversation.json');

        $finderMock->method('findFilesInDirectory')->willReturn(new ArrayIterator([$fileMock]));

        $directory    = (new DirectoryBuilder())->withId('550e8400-e29b-41d4-a716-446655440000')->build();
        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();

        $serializerMock->method('deserialize')->willReturn($conversation);

        $query = new FindConversationsByDirectoryQuery(
            $fileAccessMock,
            $serializerMock,
            $loggerMock,
            $pathRegistryMock,
            $finderMock,
        );

        $parameters = new FindConversationsByDirectoryParameters($directory);

        $result = $query->query($parameters);

        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertSame($conversation, $result[0]);
    }

    #[Test]
    public function queryFiltersConversationFronDifferentDirectory(): void
    {
        $fileAccessMock   = $this->createMock(FileAccess::class);
        $serializerMock   = $this->createMock(SerializerInterface::class);
        $loggerMock       = $this->createMock(LoggerInterface::class);
        $pathRegistryMock = $this->createMock(PathRegistry::class);
        $finderMock       = $this->createMock(Finder::class);

        $pathRegistryMock->method('get')->willReturn('/some/directory');

        $fileMock = $this->createMock(SplFileInfo::class);
        $fileMock->method('getFilename')->willReturn('conversation.json');

        $finderMock->method('findFilesInDirectory')->willReturn(new ArrayIterator([$fileMock]));

        $directory    = (new DirectoryBuilder())->withId('550e8400-e29b-41d4-a716-446655440000')->build();
        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();

        $serializerMock->method('deserialize')->willReturn($conversation);

        $query = new FindConversationsByDirectoryQuery(
            $fileAccessMock,
            $serializerMock,
            $loggerMock,
            $pathRegistryMock,
            $finderMock,
        );

        $searchedDirectory = (new DirectoryBuilder())->build();
        $parameters        = new FindConversationsByDirectoryParameters($searchedDirectory);

        $result = $query->query($parameters);

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    #[Test]
    public function parameters(): void
    {
        $directory  = (new DirectoryBuilder())->withId('550e8400-e29b-41d4-a716-446655440000')->build();
        $parameters = new FindConversationsByDirectoryParameters($directory);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $parameters->directory->id);
    }
}
