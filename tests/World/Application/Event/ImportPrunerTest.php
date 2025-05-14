<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Application\Event;

use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Command\StoreWorldItem;
use ChronicleKeeper\World\Application\Event\ImportPruner;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(ImportPruner::class)]
#[Large]
class ImportPrunerTest extends DatabaseTestCase
{
    private ImportPruner $importPruner;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $importPruner = self::getContainer()->get(ImportPruner::class);
        assert($importPruner instanceof ImportPruner);

        $this->importPruner = $importPruner;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->importPruner);
    }

    #[Test]
    public function itIsPruning(): void
    {
        // ------------------- The test setup -------------------

        $worldItem = (new ItemBuilder())->build();
        $this->bus->dispatch(new StoreWorldItem($worldItem));

        // ------------------- The test execution -------------------

        ($this->importPruner)();

        // ------------------- The test assertions -------------------

        $this->assertTableIsEmpty('world_items');
        $this->assertTableIsEmpty('world_item_relations');
        $this->assertTableIsEmpty('world_item_images');
        $this->assertTableIsEmpty('world_item_documents');
        $this->assertTableIsEmpty('world_item_conversations');
    }
}
