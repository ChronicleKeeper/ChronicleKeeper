<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\Event;

use ChronicleKeeper\Settings\Application\Service\Importer\ImportedFileBag;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Settings\Domain\Event\ImportFinished;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImportFinished::class)]
#[Small]
final class ImportFinishedTest extends TestCase
{
    #[Test]
    public function itAllowsConstruction(): void
    {
        $importSettings  = self::createStub(ImportSettings::class);
        $importedFileBag = new ImportedFileBag();

        $event = new ImportFinished($importSettings, $importedFileBag);

        self::assertSame($importSettings, $event->importSettings);
        self::assertSame($importedFileBag, $event->importedFileBag);
    }
}
