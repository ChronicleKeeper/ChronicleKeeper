<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Event;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorResult;
use ChronicleKeeper\ImageGenerator\Application\Event\ImportPruner;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorResultBuilder;
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

        $generatorRequest = (new GeneratorRequestBuilder())->build();
        $generatorResult  = (new GeneratorResultBuilder())->build();

        $this->bus->dispatch(new StoreGeneratorRequest($generatorRequest));
        $this->bus->dispatch(new StoreGeneratorResult($generatorRequest->id, $generatorResult));

        // ------------------- The test assertions -------------------

        ($this->importPruner)(new ExecuteImportPruning(new ImportSettings()));

        // ------------------- The test assertions -------------------

        $this->assertTableIsEmpty('generator_results');
        $this->assertTableIsEmpty('generator_requests');
    }
}
