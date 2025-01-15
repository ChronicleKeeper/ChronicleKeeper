<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Event;

use ChronicleKeeper\Chat\Application\Event\ImportPruner;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Settings\Domain\Event\ExecuteImportPruning;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(ImportPruner::class)]
#[Small]
class ImportPrunerTest extends TestCase
{
    #[Test]
    public function itIsPruning(): void
    {
        $databasePlatform = new DatabasePlatformMock();

        $pathRegistry = self::createStub(PathRegistry::class);
        $pathRegistry->method('get')->willReturn('/tmp');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())->method('remove')->with('/tmp/conversation_temporary.json');

        (new ImportPruner($databasePlatform, $pathRegistry, $filesystem, new NullLogger()))
            ->__invoke(new ExecuteImportPruning(new ImportSettings()));

        $databasePlatform->assertExecutedQuery('DELETE FROM conversation_settings');
        $databasePlatform->assertExecutedQuery('DELETE FROM conversation_messages');
        $databasePlatform->assertExecutedQuery('DELETE FROM conversations');
    }
}
