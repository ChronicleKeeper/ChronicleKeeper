<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Domain\ValueObject;

use ChronicleKeeper\Calendar\Domain\ValueObject\MoonState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MoonState::class)]
#[Small]
final class MoonStateTest extends TestCase
{
    #[Test]
    public function itCanDeliverTheCorrectLabels(): void
    {
        self::assertSame('Neumond', MoonState::NEW_MOON->getLabel());
        self::assertSame('Zunehmender Sichelmond', MoonState::WAXING_CRESCENT->getLabel());
        self::assertSame('Erstes Viertel', MoonState::FIRST_QUARTER->getLabel());
        self::assertSame('Zunehmender Halbmond', MoonState::WAXING_GIBBOUS->getLabel());
        self::assertSame('Vollmond', MoonState::FULL_MOON->getLabel());
        self::assertSame('Abnehmender Halbmond', MoonState::WANING_GIBBOUS->getLabel());
        self::assertSame('Letztes Viertel', MoonState::LAST_QUARTER->getLabel());
        self::assertSame('Abnehmender Sichelmond', MoonState::WANING_CRESCENT->getLabel());
    }

    #[Test]
    public function itCanDeliverTheCorrectIcons(): void
    {
        self::assertSame('🌑', MoonState::NEW_MOON->getIcon());
        self::assertSame('🌒', MoonState::WAXING_CRESCENT->getIcon());
        self::assertSame('🌓', MoonState::FIRST_QUARTER->getIcon());
        self::assertSame('🌔', MoonState::WAXING_GIBBOUS->getIcon());
        self::assertSame('🌕', MoonState::FULL_MOON->getIcon());
        self::assertSame('🌖', MoonState::WANING_GIBBOUS->getIcon());
        self::assertSame('🌗', MoonState::LAST_QUARTER->getIcon());
        self::assertSame('🌘', MoonState::WANING_CRESCENT->getIcon());
    }
}
