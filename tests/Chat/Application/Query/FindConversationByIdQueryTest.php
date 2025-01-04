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
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

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
        $conversation = new Conversation(
            '123e4567-e89b-12d3-a456-426614174000',
            'Test conversation',
            RootDirectory::get(),
            new Settings(),
            new ExtendedMessageBag(),
        );

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch(
            'SELECT * FROM conversations WHERE id = :id',
            ['id' => $conversation->getId()],
            [
                [
                    'id' => $conversation->getId(),
                    'title' => 'Test conversation',
                    'directory' => $conversation->getDirectory()->getId(),
                ],
            ],
        );
        $databasePlatform->expectFetch(
            'SELECT * FROM conversation_settings WHERE conversation_id = :id',
            ['id' => $conversation->getId()],
            [
                [
                    'conversation_id' => $conversation->getId(),
                    'version' => $conversation->getSettings()->version,
                    'temperature' => $conversation->getSettings()->temperature,
                    'images_max_distance' => $conversation->getSettings()->imagesMaxDistance,
                    'documents_max_distance' => $conversation->getSettings()->documentsMaxDistance,
                ],
            ],
        );
        $databasePlatform->expectFetch(
            'SELECT * FROM conversation_messages WHERE conversation_id = :id',
            ['id' => $conversation->getId()],
            [],
        );

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(
                [
                    'id' => $conversation->getId(),
                    'title' => 'Test conversation',
                    'directory' => $conversation->getDirectory()->getId(),
                    'settings' => [
                        'version' => $conversation->getSettings()->version,
                        'temperature' => $conversation->getSettings()->temperature,
                        'imagesMaxDistance' => $conversation->getSettings()->imagesMaxDistance,
                        'documentsMaxDistance' => $conversation->getSettings()->documentsMaxDistance,
                    ],
                    'messages' => [],
                ],
                Conversation::class,
                null,
                [
                    ExtendedMessageDenormalizer::WITH_CONTEXT_DOCUMENTS => false,
                    ExtendedMessageDenormalizer::WITH_CONTEXT_IMAGES => false,
                    ExtendedMessageDenormalizer::WITH_DEBUG_FUNCTIONS => false,
                ],
            )
            ->willReturn($conversation);

        $query  = new FindConversationByIdQuery(
            $denormalizer,
            self::createStub(SettingsHandler::class),
            $databasePlatform,
        );
        $result = $query->query(new FindConversationByIdParameters('123e4567-e89b-12d3-a456-426614174000'));

        $databasePlatform->assertFetchCount(3);
        self::assertSame($conversation, $result);
    }

    #[Test]
    public function queryReturnsNullWhenConversationNotFound(): void
    {
        $normalizer = $this->createMock(DenormalizerInterface::class);
        $normalizer->expects($this->never())->method('denormalize');

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch(
            'SELECT * FROM conversations WHERE id = :id',
            ['id' => '123e4567-e89b-12d3-a456-426614174000'],
            [],
        );

        $query  = new FindConversationByIdQuery(
            $normalizer,
            self::createStub(SettingsHandler::class),
            $databasePlatform,
        );
        $result = $query->query(new FindConversationByIdParameters('123e4567-e89b-12d3-a456-426614174000'));

        self::assertNull($result);
    }
}
