<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Event;

use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Library\Application\Event\ImportPruner;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
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

        $directory = (new DirectoryBuilder())->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        // ------------------- The test assertions -------------------

        ($this->importPruner)();

        // ------------------- The test assertions -------------------

        $this->assertTableIsEmpty('directories');
    }
}
