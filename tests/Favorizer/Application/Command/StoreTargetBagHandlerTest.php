<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Application\Command;

use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBag;
use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBagHandler;
use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(StoreTargetBag::class)]
#[CoversClass(StoreTargetBagHandler::class)]
#[Large]
class StoreTargetBagHandlerTest extends DatabaseTestCase
{
    private StoreTargetBagHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(StoreTargetBagHandler::class);
        assert($handler instanceof StoreTargetBagHandler);

        $this->handler = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handler);
    }

    #[Test]
    public function itIsAbleToStoreTheTargetBag(): void
    {
        // ------------------- The test setup -------------------

        $targetBag = new TargetBag();
        $targetBag->append(new LibraryDocumentTarget('4c0ad0b6-772d-4ef2-8fd6-8120c90e6e45', 'Title 1'));
        $targetBag->append(new LibraryImageTarget('c0773b2c-0479-4a5b-91b9-2b52b10fcde8', 'Title 2'));

        // ------------------- The test scenario -------------------

        ($this->handler)(new StoreTargetBag($targetBag));

        // ------------------- The test assertions -------------------

        $this->assertRowsInTable('favorites', 2);
    }
}
