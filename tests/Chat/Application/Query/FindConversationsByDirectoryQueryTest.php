<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryQuery;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Infrastructure\Database\Converter\DatabaseRowConverter;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(FindConversationsByDirectoryQuery::class)]
#[CoversClass(FindConversationsByDirectoryParameters::class)]
#[Small]
class FindConversationsByDirectoryQueryTest extends TestCase
{
    #[Test]
    public function itEnsuresTheParametersHasTheCorrectQueryClass(): void
    {
        self::assertSame(
            FindConversationsByDirectoryQuery::class,
            (new FindConversationsByDirectoryParameters(RootDirectory::get()))->getQueryClass(),
        );
    }

    #[Test]
    public function queryReturnsConversations(): void
    {
        $directory    = (new DirectoryBuilder())->withId('550e8400-e29b-41d4-a716-446655440000')->build();
        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->once())->method('denormalize')->willReturn($conversation);

        $databasePlatformMock = new DatabasePlatformMock();
        $databasePlatformMock->expectFetch(
            'SELECT * FROM conversations WHERE directory = :directory ORDER BY title',
            ['directory' => $directory->getId()],
            [
                [
                    'id'        => $conversation->getId(),
                    'title'     => 'Test conversation',
                    'directory' => $directory->getId(),
                ],
            ],
        );

        $databaseRowConverter = $this->createMock(DatabaseRowConverter::class);
        $databaseRowConverter->expects($this->once())->method('convert')->willReturn([
            'id'        => $conversation->getId(),
            'title'     => 'Test conversation',
            'directory' => $directory->getId(),
        ]);

        $query      = new FindConversationsByDirectoryQuery(
            $denormalizer,
            $databasePlatformMock,
            $databaseRowConverter,
        );
        $parameters = new FindConversationsByDirectoryParameters($directory);

        $result = $query->query($parameters);

        self::assertCount(1, $result);
        self::assertSame($conversation, $result[0]);
    }

    #[Test]
    public function parameters(): void
    {
        $directory  = (new DirectoryBuilder())->withId('550e8400-e29b-41d4-a716-446655440000')->build();
        $parameters = new FindConversationsByDirectoryParameters($directory);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $parameters->directory->getId());
    }
}
