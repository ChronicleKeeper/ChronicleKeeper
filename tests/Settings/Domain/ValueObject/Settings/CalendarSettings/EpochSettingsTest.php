<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\ValueObject\Settings\CalendarSettings;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\EpochSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(EpochSettings::class)]
#[Small]
final class EpochSettingsTest extends TestCase
{
    #[Test]
    public function itConstructsFromArray(): void
    {
        $data = [
            'name' => 'First Age',
            'start_year' => 1,
            'end_year' => 100,
        ];

        $settings = EpochSettings::fromArray($data);

        self::assertSame('First Age', $settings->getName());
        self::assertSame(1, $settings->getStartYear());
        self::assertSame(100, $settings->getEndYear());
    }

    #[Test]
    public function itConvertsToArray(): void
    {
        $settings = new EpochSettings('First Age', 1, 100);

        $expected = [
            'name' => 'First Age',
            'start_year' => 1,
            'end_year' => 100,
        ];

        self::assertSame($expected, $settings->toArray());
    }

    #[Test]
    public function itSerializesToJson(): void
    {
        $settings = new EpochSettings('First Age', 1, 100);

        $expected = [
            'name' => 'First Age',
            'start_year' => 1,
            'end_year' => 100,
        ];

        self::assertSame(
            json_encode($expected, JSON_THROW_ON_ERROR),
            json_encode($settings, JSON_THROW_ON_ERROR),
        );
    }
}
