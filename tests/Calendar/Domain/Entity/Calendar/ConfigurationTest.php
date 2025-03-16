<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\Entity\Calendar;

use ChronicleKeeper\Calendar\Domain\Entity\Calendar\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Configuration::class)]
#[Small]
final class ConfigurationTest extends TestCase
{
    #[Test]
    public function itCreatesConfigurationWithDefaultBeginsInYear(): void
    {
        $configuration = new Configuration();

        self::assertSame(0, $configuration->beginsInYear);
    }

    #[Test]
    public function itCreatesConfigurationWithCustomBeginsInYear(): void
    {
        $configuration = new Configuration(beginsInYear: 42);

        self::assertSame(42, $configuration->beginsInYear);
    }
}
