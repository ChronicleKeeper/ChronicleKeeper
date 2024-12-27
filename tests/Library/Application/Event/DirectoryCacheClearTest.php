<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Event;

use ChronicleKeeper\Library\Application\Event\DirectoryCacheClear;
use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFileBag;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Settings\Domain\Event\ImportFinished;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DirectoryCacheClear::class)]
#[Small]
final class DirectoryCacheClearTest extends TestCase
{
    #[Test]
    public function itClearsTheCache(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())->method('prune')->with('library.directories.cache');

        $event               = new ImportFinished(new ImportSettings(), new ImportedFileBag());
        $directoryCacheClear = new DirectoryCacheClear($fileAccess);
        $directoryCacheClear($event);
    }
}
