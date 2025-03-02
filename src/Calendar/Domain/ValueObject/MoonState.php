<?php

declare(strict_types=1);

namespace ChronicleKeeper\Calendar\Domain\ValueObject;

enum MoonState: string
{
    case FIRST_QUARTER   = 'first_quarter';
    case FULL_MOON       = 'full_moon';
    case LAST_QUARTER    = 'last_quarter';
    case NEW_MOON        = 'new_moon';
    case WANING_CRESCENT = 'waning_crescent';
    case WANING_GIBBOUS  = 'waning_gibbous';
    case WAXING_CRESCENT = 'waxing_crescent';
    case WAXING_GIBBOUS  = 'waxing_gibbous';

    public function getLabel(): string
    {
        return match ($this) {
            self::FIRST_QUARTER => 'Erstes Viertel',
            self::FULL_MOON => 'Vollmond',
            self::LAST_QUARTER => 'Letztes Viertel',
            self::NEW_MOON => 'Neumond',
            self::WANING_CRESCENT => 'Abnehmender Sichelmond',
            self::WANING_GIBBOUS => 'Abnehmender Halbmond',
            self::WAXING_CRESCENT => 'Zunehmender Sichelmond',
            self::WAXING_GIBBOUS => 'Zunehmender Halbmond',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::NEW_MOON => '🌑',
            self::WAXING_CRESCENT => '🌒',
            self::FIRST_QUARTER => '🌓',
            self::WAXING_GIBBOUS => '🌔',
            self::FULL_MOON => '🌕',
            self::WANING_GIBBOUS => '🌖',
            self::LAST_QUARTER => '🌗',
            self::WANING_CRESCENT => '🌘',
        };
    }
}
