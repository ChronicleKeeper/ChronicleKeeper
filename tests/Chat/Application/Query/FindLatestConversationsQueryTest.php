<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ChronicleKeeper\Chat\Application\Query\FindLatestConversationsParameters;
use ChronicleKeeper\Chat\Application\Query\FindLatestConversationsQuery;
use ChronicleKeeper\Shared\Infrastructure\Database\Converter\DatabaseRowConverter;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(FindLatestConversationsQuery::class)]
#[CoversClass(FindLatestConversationsParameters::class)]
#[Small]
class FindLatestConversationsQueryTest extends TestCase
{
    #[Test]
    public function itEnsuresTheParametersHasTheCorrectQueryClass(): void
    {
        self::assertSame(
            FindLatestConversationsQuery::class,
            (new FindLatestConversationsParameters(12))->getQueryClass(),
        );
    }

    #[Test]
    public function queryReturnsConversations(): void
    {
        $conversation = (new ConversationBuilder())->build();

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->once())->method('denormalize')->willReturn($conversation);

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch(
            'SELECT * FROM conversations ORDER BY title LIMIT :limit',
            ['limit' => 1],
            [
                [
                    'id'        => $conversation->getId(),
                    'title'     => 'Test conversation',
                    'directory' => $conversation->getDirectory()->getId(),
                ],
            ],
        );

        $databaseRowConverter = $this->createMock(DatabaseRowConverter::class);
        $databaseRowConverter->expects($this->once())->method('convert')->willReturn([
            'id'        => $conversation->getId(),
            'title'     => 'Test conversation',
            'directory' => $conversation->getDirectory()->getId(),
        ]);

        $query = new FindLatestConversationsQuery($denormalizer, $databasePlatform, $databaseRowConverter);
        $query->query(new FindLatestConversationsParameters(1));
    }

    #[Test]
    public function parameters(): void
    {
        $parameters = new FindLatestConversationsParameters(1);

        self::assertSame(1, $parameters->maxEntries);
    }
}
