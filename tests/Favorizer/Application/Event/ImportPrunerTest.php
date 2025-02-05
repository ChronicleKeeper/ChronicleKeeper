<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Application\Event;

use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBag;
use ChronicleKeeper\Favorizer\Application\Event\ImportPruner;
use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\SQLite\DatabaseTestCase;
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

        $targetBag = new TargetBag();
        $targetBag->append(new LibraryDocumentTarget('4c0ad0b6-772d-4ef2-8fd6-8120c90e6e45', 'Title 1'));
        $targetBag->append(new LibraryImageTarget('c0773b2c-0479-4a5b-91b9-2b52b10fcde8', 'Title 2'));

        $this->bus->dispatch(new StoreTargetBag($targetBag));

        // ------------------- The test execution -------------------

        ($this->importPruner)(new ExecuteImportPruning(new ImportSettings()));

        // ------------------- The test assertions -------------------

        $this->assertTableIsEmpty('favorites');
    }
}
