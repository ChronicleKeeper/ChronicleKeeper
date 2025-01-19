<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Domain\ValueObject;

enum ItemType: string
{
    case COUNTRY  = 'country';
    case REGION   = 'region';
    case LOCATION = 'location';

    case ORGANIZATION = 'organization';
    case PERSON       = 'person';
    case RACE         = 'race';

    case EVENT    = 'event';
    case QUEST    = 'quest';
    case CAMPAIGN = 'campaign';

    case OBJECT = 'object';
    case OTHER  = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::COUNTRY => 'Land',
            self::REGION => 'Region',
            self::LOCATION => 'Ort',
            self::ORGANIZATION => 'Organisation',
            self::PERSON => 'Person',
            self::RACE => 'Rasse',
            self::EVENT => 'Ereignis',
            self::QUEST => 'Quest',
            self::CAMPAIGN => 'Kampagne',
            self::OBJECT => 'Objekt',
            self::OTHER => 'Sonstiges',
        };
    }
}
