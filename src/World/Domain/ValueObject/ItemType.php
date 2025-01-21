<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Domain\ValueObject;

use function array_keys;

enum ItemType: string
{
    case COUNTRY  = 'country';
    case REGION   = 'region';
    case LOCATION = 'location';

    case ORGANIZATION = 'organization';
    case PERSON       = 'person';

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
            self::EVENT => 'Ereignis',
            self::QUEST => 'Quest',
            self::CAMPAIGN => 'Kampagne',
            self::OBJECT => 'Objekt',
            self::OTHER => 'Sonstiges',
        };
    }

    /** @return list<string> */
    public static function getPossibleRelationsForType(ItemType $sourceType): array
    {
        return array_keys(self::getRelationTypesTo()[$sourceType->value]);
    }

    /**
     * The array is build in the kind that each single array below contains the following values:
     *
     * 1. Source type
     * 2. Target type
     * 3. Relation type
     * 4. Label for source type
     * 5. Label for target type
     *
     * Those values will be used to build the relation between two items. In the final mapping each source type
     * will also have a mapping to the target type with the relation type as key and the label as value.
     *
     * @return list<array{0: ItemType, 1: ItemType, 2: string, 3: string, 4: string}>
     */
    public static function getRelationTypes(): array
    {
        return [
            [self::COUNTRY, self::COUNTRY, 'related', 'Beziehung zu', 'Beziehung zu'],
            [self::COUNTRY, self::REGION, 'governed', 'verwaltet', 'verwaltet von'],
            [self::COUNTRY, self::LOCATION, 'governed', 'verwaltet', 'verwaltet von'],
            [self::COUNTRY, self::ORGANIZATION, 'governed', 'verwaltet', 'verwaltet von'],
            [self::COUNTRY, self::PERSON, 'governed', 'verwaltet von', 'verwaltet'],

            [self::REGION, self::REGION, 'related', 'Beziehung zu', 'Beziehung zu'],
            [self::REGION, self::LOCATION, 'governed', 'verwaltet', 'verwaltet von'],
            [self::COUNTRY, self::ORGANIZATION, 'governed', 'verwaltet', 'verwaltet von'],
            [self::COUNTRY, self::PERSON, 'governed', 'verwaltet von', 'verwaltet'],

            [self::LOCATION, self::LOCATION, 'related', 'Beziehung zu', 'Beziehung zu'],
            [self::LOCATION, self::ORGANIZATION, 'governed', 'verwaltet', 'verwaltet von'],
            [self::LOCATION, self::PERSON, 'governed', 'verwaltet von', 'verwaltet'],

            [self::ORGANIZATION, self::ORGANIZATION, 'related', 'Beziehung zu', 'Beziehung zu'],
            [self::ORGANIZATION, self::PERSON, 'member', 'hat Mitglied', 'Mitglied von'],
            [self::ORGANIZATION, self::OBJECT, 'owns', 'besitzt', 'gehört'],

            [self::PERSON, self::PERSON, 'related', 'Beziehung zu', 'Beziehung zu'],
            [self::PERSON, self::OBJECT, 'owns', 'besitzt', 'gehört'],
        ];
    }

    /**
     * This array is build from the getRelationTypes() array and will be used to build the relation between two items.
     * The array is build in the kind that each single array below contains the following values:
     *
     * @return array<string, array<string, array<string, string>>>
     */
    public static function getRelationTypesTo(): array
    {
        $relations     = self::getRelationTypes();
        $relationTypes = [];
        foreach ($relations as [$sourceType, $targetType, $relationType, $sourceLabel, $targetLabel]) {
            $relationTypes[$sourceType->value][$targetType->value][$relationType] = $sourceLabel;
            $relationTypes[$targetType->value][$sourceType->value][$relationType] = $targetLabel;
        }

        // Additionally every available type within this enum gets a default "related" relation to every other type
        foreach (self::cases() as $sourceType) {
            foreach (self::cases() as $targetType) {
                $relationTypes[$sourceType->value][$targetType->value]['related'] = 'Beziehung zu';
            }
        }

        return $relationTypes;
    }

    public function getRelationLabelTo(ItemType $targetType, string $relationType): string
    {
        return self::getRelationTypesTo()[$this->value][$targetType->value][$relationType];
    }
}
