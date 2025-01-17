<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdQuery;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\Database\Converter\DatabaseRowConverter;
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

        $databaseRowConverter = $this->createMock(DatabaseRowConverter::class);
        $databaseRowConverter->expects($this->once())->method('convert')->willReturn([
            'id' => $conversation->getId(),
            'title' => 'Test conversation',
            'directory' => $conversation->getDirectory()->getId(),
        ]);

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->once())->method('denormalize')->willReturn($conversation);

        $query  = new FindConversationByIdQuery(
            $denormalizer,
            self::createStub(SettingsHandler::class),
            $databasePlatform,
            $databaseRowConverter,
        );
        $result = $query->query(new FindConversationByIdParameters('123e4567-e89b-12d3-a456-426614174000'));

        $databasePlatform->assertFetchCount(1);
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

        $databaseRowConverter = $this->createMock(DatabaseRowConverter::class);
        $databaseRowConverter->expects($this->never())->method('convert');

        $query  = new FindConversationByIdQuery(
            $normalizer,
            self::createStub(SettingsHandler::class),
            $databasePlatform,
            $databaseRowConverter,
        );
        $result = $query->query(new FindConversationByIdParameters('123e4567-e89b-12d3-a456-426614174000'));

        self::assertNull($result);
    }
}
