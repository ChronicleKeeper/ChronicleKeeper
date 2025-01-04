<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ChronicleKeeper\Chat\Application\Query\FindLatestConversationsParameters;
use ChronicleKeeper\Chat\Application\Query\FindLatestConversationsQuery;
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

        $databasePlatform->expectFetch(
            'SELECT * FROM conversation_settings WHERE conversation_id = :id',
            ['id' => $conversation->getId()],
            [
                [
                    'conversation_id'      => $conversation->getId(),
                    'version'              => 1,
                    'temperature'          => 1,
                    'images_max_distance'  => 1,
                    'documents_max_distance' => 1,
                ],
            ],
        );

        $databasePlatform->expectFetch(
            'SELECT * FROM conversation_messages WHERE conversation_id = :id',
            ['id' => $conversation->getId()],
            [
                [
                    'id' => '123e4567-e89b-12d3-a456-426614174000',
                    'conversation_id' => $conversation->getId(),
                    'role' => 'system',
                    'content' => 'Test message',
                    'context' => '{}',
                    'debug' => '{}',
                ],
            ],
        );

        $query = new FindLatestConversationsQuery($denormalizer, $databasePlatform);
        $query->query(new FindLatestConversationsParameters(1));
    }

    #[Test]
    public function parameters(): void
    {
        $parameters = new FindLatestConversationsParameters(1);

        self::assertSame(1, $parameters->maxEntries);
    }
}
