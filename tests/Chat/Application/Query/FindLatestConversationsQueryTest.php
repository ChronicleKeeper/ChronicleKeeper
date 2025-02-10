<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Query\FindLatestConversationsParameters;
use ChronicleKeeper\Chat\Application\Query\FindLatestConversationsQuery;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBagBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\AssistantMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\SystemMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\UserMessageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindLatestConversationsQuery::class)]
#[CoversClass(FindLatestConversationsParameters::class)]
#[Large]
class FindLatestConversationsQueryTest extends DatabaseTestCase
{
    private FindLatestConversationsQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindLatestConversationsQuery::class);
        assert($query instanceof FindLatestConversationsQuery);

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
            FindLatestConversationsQuery::class,
            (new FindLatestConversationsParameters(12))->getQueryClass(),
        );
    }

    #[Test]
    public function itWillReturnAListOfConversations(): void
    {
        // ------------------- The test setup -------------------
        $firstConversation = (new ConversationBuilder())
            ->withId('123e4567-e89b-12d3-a456-426614174000')
            ->withTitle('Test conversation')
            ->withMessages((new ExtendedMessageBagBuilder())
                ->withMessages(
                    (new ExtendedMessageBuilder())->withMessage((new SystemMessageBuilder())->build())->build(),
                    (new ExtendedMessageBuilder())->withMessage((new UserMessageBuilder())->build())->build(),
                    (new ExtendedMessageBuilder())->withMessage((new AssistantMessageBuilder())->build())->build(),
                )
                ->build())
            ->build();

        $secondConversation = (new ConversationBuilder())
            ->withId('ddb44c62-4eae-4411-a421-0c56927c9e2b')
            ->withTitle('Test conversation 2')
            ->withMessages((new ExtendedMessageBagBuilder())
                ->withMessages(
                    (new ExtendedMessageBuilder())->withMessage((new SystemMessageBuilder())->build())->build(),
                    (new ExtendedMessageBuilder())->withMessage((new UserMessageBuilder())->build())->build(),
                    (new ExtendedMessageBuilder())->withMessage((new AssistantMessageBuilder())->build())->build(),
                )
                ->build())
            ->build();

        $this->bus->dispatch(new StoreConversation($firstConversation));
        $this->bus->dispatch(new StoreConversation($secondConversation));

        // ------------------- The test execution -------------------

        $result = $this->query->query(new FindLatestConversationsParameters(1));

        // ------------------- The test assertions -------------------

        self::assertCount(1, $result);
        self::assertSame($firstConversation->getId(), $result[0]->getId());
        self::assertSame($firstConversation->getTitle(), $result[0]->getTitle());
    }

    #[Test]
    public function itCanConstructAValidCommand(): void
    {
        $parameters = new FindLatestConversationsParameters(1);

        self::assertSame(1, $parameters->maxEntries);
    }
}
