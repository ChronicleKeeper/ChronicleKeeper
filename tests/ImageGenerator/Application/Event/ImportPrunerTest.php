<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Event;

use ChronicleKeeper\ImageGenerator\Application\Event\ImportPruner;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(ImportPruner::class)]
#[Small]
class ImportPrunerTest extends TestCase
{
    #[Test]
    public function itIsPruning(): void
    {
        $databasePlatform = new DatabasePlatformMock();

        (new ImportPruner($databasePlatform, new NullLogger()))
            ->__invoke(new ExecuteImportPruning(new ImportSettings()));

        $databasePlatform->assertExecutedQuery('DELETE FROM generator_results');
        $databasePlatform->assertExecutedQuery('DELETE FROM generator_requests');
    }
}
