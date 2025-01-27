<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Application\Event;

use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use ChronicleKeeper\World\Application\Event\ImportPruner;
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

        $databasePlatform->assertExecutedQuery('DELETE FROM world_item_conversations');
        $databasePlatform->assertExecutedQuery('DELETE FROM world_item_documents');
        $databasePlatform->assertExecutedQuery('DELETE FROM world_item_images');
        $databasePlatform->assertExecutedQuery('DELETE FROM world_item_relations');
        $databasePlatform->assertExecutedQuery('DELETE FROM world_items');
    }
}
