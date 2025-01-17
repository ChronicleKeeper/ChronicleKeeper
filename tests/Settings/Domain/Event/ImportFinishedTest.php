<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\Event;

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
        $importSettings = self::createStub(ImportSettings::class);

        $event = new ImportFinished($importSettings);

        self::assertSame($importSettings, $event->importSettings);
    }
}
