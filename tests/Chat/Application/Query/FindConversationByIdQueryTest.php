<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdQuery;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Infrastructure\Database\Converter\ConversationRowConverter;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBagBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\AssistantMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\SystemMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\UserMessageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindConversationByIdQuery::class)]
#[CoversClass(FindConversationByIdParameters::class)]
#[CoversClass(ConversationRowConverter::class)]
#[Large]
class FindConversationByIdQueryTest extends DatabaseTestCase
{
    private FindConversationByIdQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindConversationByIdQuery::class);
        assert($query instanceof FindConversationByIdQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itEnsuresTheParametersHasTheCorrectQueryClass(): void
    {
        self::assertSame(
            FindConversationByIdQuery::class,
            (new FindConversationByIdParameters('123e4567-e89b-12d3-a456-426614174000'))->getQueryClass(),
        );
    }

    #[Test]
    public function itCanFetchASpecificConversation(): void
    {
        // ------------------- The test setup -------------------
        $messages = (new ExtendedMessageBagBuilder())
            ->withMessages(
                (new ExtendedMessageBuilder())->withMessage((new SystemMessageBuilder())->build())->build(),
                (new ExtendedMessageBuilder())->withMessage((new UserMessageBuilder())->build())->build(),
                (new ExtendedMessageBuilder())->withMessage((new AssistantMessageBuilder())->build())->build(),
            )
            ->build();

        $conversation = (new ConversationBuilder())
            ->withId('123e4567-e89b-12d3-a456-426614174000')
            ->withTitle('Test conversation')
            ->withMessages($messages)
            ->build();

        $this->bus->dispatch(new StoreConversation($conversation));

        // ------------------- The test execution -------------------

        $result = $this->query->query(new FindConversationByIdParameters($conversation->getId()));

        // ------------------- The test assertions -------------------

        self::assertInstanceOf(Conversation::class, $result);
        self::assertSame($conversation->getId(), $result->getId());
        self::assertSame($conversation->getTitle(), $result->getTitle());
        self::assertEquals($conversation->getMessages(), $result->getMessages());
    }

    #[Test]
    public function theQueryReturnsNullWhenConversationNotFound(): void
    {
        // ------------------- The test execution -------------------

        $result = $this->query->query(new FindConversationByIdParameters('123e4567-e89b-12d3-a456-426614174000'));

        // ------------------- The test assertions -------------------

        self::assertNull($result);
    }
}
