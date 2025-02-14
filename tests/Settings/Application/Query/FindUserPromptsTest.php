<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Query;

use ChronicleKeeper\Settings\Application\Query\FindUserPrompts;
use ChronicleKeeper\Settings\Application\Query\FindUserPromptsQuery;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(FindUserPrompts::class)]
#[CoversClass(FindUserPromptsQuery::class)]
#[Large]
final class FindUserPromptsTest extends DatabaseTestCase
{
    private FindUserPromptsQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(FindUserPromptsQuery::class);
        assert($query instanceof FindUserPromptsQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itHasACorrectQueryClassInUse(): void
    {
        self::assertSame(FindUserPromptsQuery::class, (new FindUserPrompts())->getQueryClass());
    }

    #[Test]
    public function itIsAbleToFetchAllUserPrompts(): void
    {
        // ------------------- The test setup     -------------------

        // -> Preparation will come when migration to database is done

        // ------------------- The test execution -------------------

        $results = $this->query->query(new FindUserPrompts());

        // ------------------- The test assertions -------------------

        self::assertEmpty($results);
    }
}
