<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Query;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryQuery;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBagBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\AssistantMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\SystemMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\UserMessageBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindConversationsByDirectoryQuery::class)]
#[CoversClass(FindConversationsByDirectoryParameters::class)]
#[Large]
class FindConversationsByDirectoryQueryTest extends DatabaseTestCase
{
    private FindConversationsByDirectoryQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindConversationsByDirectoryQuery::class);
        assert($query instanceof FindConversationsByDirectoryQuery);

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
            FindConversationsByDirectoryQuery::class,
            (new FindConversationsByDirectoryParameters(RootDirectory::get()))->getQueryClass(),
        );
    }

    #[Test]
    public function queryReturnsConversations(): void
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
        $result = $this->query->query(new FindConversationsByDirectoryParameters($conversation->getDirectory()));

        self::assertCount(1, $result);
        self::assertSame($conversation->getId(), $result[0]->getId());
        self::assertSame($conversation->getTitle(), $result[0]->getTitle());
    }

    #[Test]
    public function parameters(): void
    {
        $directory  = (new DirectoryBuilder())->withId('550e8400-e29b-41d4-a716-446655440000')->build();
        $parameters = new FindConversationsByDirectoryParameters($directory);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $parameters->directory->getId());
    }
}
