<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Application\Query;

use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBag;
use ChronicleKeeper\Favorizer\Application\Query\GetTargetBag;
use ChronicleKeeper\Favorizer\Application\Query\GetTargetBagQuery;
use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(GetTargetBagQuery::class)]
#[CoversClass(GetTargetBag::class)]
#[Large]
class GetTargetBagQueryTest extends DatabaseTestCase
{
    private GetTargetBagQuery $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $query = self::getContainer()->get(GetTargetBagQuery::class);
        assert($query instanceof GetTargetBagQuery);

        $this->query = $query;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->query);
    }

    #[Test]
    public function itIsUtilizingTheCorrectQueryClass(): void
    {
        self::assertSame(GetTargetBagQuery::class, (new GetTargetBag())->getQueryClass());
    }

    #[Test]
    public function itIsCreatingAnEmptyTargetBag(): void
    {
        $targetBag = $this->query->query(new GetTargetBag());

        self::assertCount(0, $targetBag);
    }

    #[Test]
    public function itIsLoadingExistingTargets(): void
    {
        // ------------------- The test setup -------------------

        $targetBag = new TargetBag();
        $targetBag->append(new LibraryDocumentTarget('4c0ad0b6-772d-4ef2-8fd6-8120c90e6e45', 'Title 1'));
        $targetBag->append(new LibraryImageTarget('c0773b2c-0479-4a5b-91b9-2b52b10fcde8', 'Title 2'));

        $this->bus->dispatch(new StoreTargetBag($targetBag));

        // ------------------- The test execution -------------------

        $targetBag = $this->query->query(new GetTargetBag());

        // ------------------- The test assertions -------------------

        self::assertCount(2, $targetBag);
        self::assertContainsOnlyInstancesOf(Target::class, $targetBag);

        $firstTarget = $targetBag[0];
        assert($firstTarget instanceof Target);
        self::assertSame('4c0ad0b6-772d-4ef2-8fd6-8120c90e6e45', $firstTarget->getId());

        $secondTarget = $targetBag[1];
        assert($secondTarget instanceof Target);
        self::assertSame('c0773b2c-0479-4a5b-91b9-2b52b10fcde8', $secondTarget->getId());
    }
}
