<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Domain\ValueObject;

use function array_keys;
use function array_merge;

enum ItemType: string
{
    // Geographic & Political
    case COUNTRY    = 'country';
    case REGION     = 'region';
    case SETTLEMENT = 'settlement';
    case LOCATION   = 'location';
    case LANDMARK   = 'landmark';

    // Beings
    case PERSON   = 'person';
    case CREATURE = 'creature';

    // Organizations
    case ORGANIZATION = 'organization';
    case FACTION      = 'faction';

    // Story & Campaign
    case EVENT    = 'event';
    case QUEST    = 'quest';
    case CAMPAIGN = 'campaign';

    // Religious & Mystical
    case DEITY    = 'deity';
    case RELIGION = 'religion';
    case RITUAL   = 'ritual';
    case SPELL    = 'spell';

    // Physical Items
    case OBJECT     = 'object';                  // Base type for all physical items
    case ARTIFACT   = 'artifact';              // Magical or unique items
    case WEAPON     = 'weapon';                  // Combat equipment
    case ARMOR      = 'armor';                    // Protective equipment
    case TOOL       = 'tool';                      // Utility items
    case DOCUMENT   = 'document';              // Written materials
    case CONTAINER  = 'container';            // Storage items
    case VALUABLE   = 'valuable';              // Precious items
    case CONSUMABLE = 'consumable';          // Used-up items

    // Other
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            // Geographic & Political
            self::COUNTRY => 'Land',
            self::REGION => 'Region',
            self::SETTLEMENT => 'Siedlung',
            self::LOCATION => 'Ort',
            self::LANDMARK => 'Wahrzeichen',

            // Beings
            self::PERSON => 'Person',
            self::CREATURE => 'Kreatur',

            // Organizations
            self::ORGANIZATION => 'Organisation',
            self::FACTION => 'Fraktion',

            // Story & Campaign
            self::EVENT => 'Ereignis',
            self::QUEST => 'Quest',
            self::CAMPAIGN => 'Kampagne',

            // Religious & Mystical
            self::DEITY => 'Gottheit',
            self::RELIGION => 'Religion',
            self::RITUAL => 'Ritual',
            self::SPELL => 'Zauber',

            // Physical Items
            self::OBJECT => 'Sonstiges Objekt',
            self::ARTIFACT => 'Artefakt',
            self::WEAPON => 'Waffe',
            self::ARMOR => 'Rüstung',
            self::TOOL => 'Werkzeug',
            self::DOCUMENT => 'Dokument',
            self::CONTAINER => 'Behälter',
            self::VALUABLE => 'Wertgegenstand',
            self::CONSUMABLE => 'Verbrauchsgegenstand',

            // Other
            self::OTHER => 'Sonstiges',
        };
    }

    public function isObjectType(): bool
    {
        return match ($this) {
            self::OBJECT,
            self::ARTIFACT,
            self::WEAPON,
            self::ARMOR,
            self::TOOL,
            self::DOCUMENT,
            self::CONTAINER,
            self::VALUABLE,
            self::CONSUMABLE => true,
            default => false,
        };
    }

    /**
     * Returns item types grouped by category for form display
     *
     * @return array<string, array<ItemType>>
     */
    public static function getGroupedTypes(): array
    {
        return [
            'Geografisch & Politisch' => [
                self::COUNTRY,
                self::REGION,
                self::SETTLEMENT,
                self::LOCATION,
                self::LANDMARK,
            ],
            'Wesen' => [
                self::PERSON,
                self::CREATURE,
            ],
            'Organisationen' => [
                self::ORGANIZATION,
                self::FACTION,
            ],
            'Geschichte & Abenteuer' => [
                self::EVENT,
                self::QUEST,
                self::CAMPAIGN,
            ],
            'Mystisches & Religion' => [
                self::DEITY,
                self::RELIGION,
                self::RITUAL,
                self::SPELL,
            ],
            'Gegenstände' => [
                self::OBJECT,
                self::ARTIFACT,
                self::WEAPON,
                self::ARMOR,
                self::TOOL,
                self::DOCUMENT,
                self::CONTAINER,
                self::VALUABLE,
                self::CONSUMABLE,
            ],
            'Sonstiges' => [
                self::OTHER,
            ],
        ];
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
        $baseRelations = [
            // Political & Diplomatic Relations
            [self::COUNTRY, self::COUNTRY, 'allied', 'Verbündeter von', 'Verbündeter mit'],
            [self::COUNTRY, self::COUNTRY, 'hostile', 'verfeindet mit', 'verfeindet mit'],
            [self::COUNTRY, self::COUNTRY, 'vassal', 'Oberherr von', 'Vasall von'],
            [self::COUNTRY, self::COUNTRY, 'protectorate', 'Schutzmacht von', 'Protektorat von'],
            [self::COUNTRY, self::COUNTRY, 'tributary', 'erhält Tribut von', 'zahlt Tribut an'],
            [self::COUNTRY, self::COUNTRY, 'puppet_state', 'kontrolliert', 'abhängig von'],
            [self::COUNTRY, self::COUNTRY, 'non_aggression', 'Nichtangriffspakt mit', 'Nichtangriffspakt mit'],
            [self::COUNTRY, self::COUNTRY, 'military_access', 'Durchmarschrecht in', 'gewährt Durchmarschrecht'],

            [self::COUNTRY, self::FACTION, 'supports', 'unterstützt', 'unterstützt von'],
            [self::COUNTRY, self::FACTION, 'outlaws', 'verbietet', 'verboten in'],
            [self::COUNTRY, self::ORGANIZATION, 'recognizes', 'erkennt an', 'anerkannt von'],
            [self::COUNTRY, self::ORGANIZATION, 'sanctions', 'sanktioniert', 'sanktioniert von'],

            [self::COUNTRY, self::PERSON, 'exiles', 'verbannt', 'verbannt von'],
            [self::COUNTRY, self::PERSON, 'grants_asylum', 'gewährt Asyl', 'unter Schutz von'],
            [self::COUNTRY, self::PERSON, 'diplomatic_immunity', 'gewährt Immunität', 'immun in'],

            [self::SETTLEMENT, self::COUNTRY, 'capital', 'Hauptstadt von', 'hat Hauptstadt'],
            [self::SETTLEMENT, self::COUNTRY, 'autonomous', 'autonom in', 'hat autonome Stadt'],
            [self::SETTLEMENT, self::COUNTRY, 'rebellion', 'rebelliert gegen', 'hat Aufstand in'],

            [self::REGION, self::COUNTRY, 'disputed', 'umstritten mit', 'beansprucht'],
            [self::REGION, self::COUNTRY, 'autonomous', 'autonom in', 'gewährt Autonomie'],
            [self::REGION, self::COUNTRY, 'occupied', 'besetzt von', 'besetzt'],

            // Geographic Relations
            [self::COUNTRY, self::COUNTRY, 'borders', 'grenzt an', 'grenzt an'],
            [self::REGION, self::REGION, 'borders', 'grenzt an', 'grenzt an'],
            [self::LOCATION, self::LOCATION, 'near', 'in der Nähe von', 'in der Nähe von'],
            [self::LOCATION, self::LOCATION, 'route', 'verbunden mit', 'verbunden mit'],

            // Administrative Relations
            [self::COUNTRY, self::REGION, 'governed', 'verwaltet', 'verwaltet von'],
            [self::COUNTRY, self::LOCATION, 'governed', 'verwaltet', 'verwaltet von'],
            [self::COUNTRY, self::ORGANIZATION, 'governed', 'verwaltet', 'verwaltet von'],
            [self::COUNTRY, self::PERSON, 'governed', 'verwaltet von', 'verwaltet'],
            [self::REGION, self::LOCATION, 'governed', 'verwaltet', 'verwaltet von'],
            [self::LOCATION, self::ORGANIZATION, 'governed', 'verwaltet', 'verwaltet von'],
            [self::LOCATION, self::PERSON, 'governed', 'verwaltet von', 'verwaltet'],

            // Economic & Trade Relations (currency relations removed)
            [self::COUNTRY, self::COUNTRY, 'trade', 'Handelsbeziehung mit', 'Handelsbeziehung mit'],
            [self::ORGANIZATION, self::ORGANIZATION, 'trade', 'Handelsbeziehung mit', 'Handelsbeziehung mit'],
            [self::ORGANIZATION, self::ORGANIZATION, 'subsidiary', 'Muttergesellschaft von', 'Tochtergesellschaft von'],
            [self::ORGANIZATION, self::ORGANIZATION, 'competitor', 'Konkurrent von', 'Konkurrent von'],

            // Personal Relations
            [self::PERSON, self::PERSON, 'family', 'verwandt mit', 'verwandt mit'],
            [self::PERSON, self::PERSON, 'friend', 'befreundet mit', 'befreundet mit'],
            [self::PERSON, self::PERSON, 'enemy', 'verfeindet mit', 'verfeindet mit'],
            [self::PERSON, self::PERSON, 'mentor', 'Mentor von', 'Schüler von'],
            [self::PERSON, self::PERSON, 'spouse', 'verheiratet mit', 'verheiratet mit'],
            [self::ORGANIZATION, self::PERSON, 'member', 'hat Mitglied', 'Mitglied von'],
            [self::PERSON, self::ORGANIZATION, 'leads', 'führt', 'geführt von'],
            [self::PERSON, self::ORGANIZATION, 'infiltrates', 'unterwandert', 'unterwandert von'],
            [self::PERSON, self::PERSON, 'pressures', 'übt Druck aus auf', 'unter Druck von'],
            [self::PERSON, self::PERSON, 'trades', 'handelt mit', 'handelt mit'],
            [self::PERSON, self::PERSON, 'informs', 'informiert', 'informiert von'],

            // Property & Ownership
            [self::ORGANIZATION, self::OBJECT, 'owns', 'besitzt', 'gehört'],
            [self::PERSON, self::OBJECT, 'owns', 'besitzt', 'gehört'],
            [self::OBJECT, self::OBJECT, 'part', 'Teil von', 'enthält'],

            // Location Based
            [self::PERSON, self::LOCATION, 'lives', 'wohnt in', 'Wohnort von'],
            [self::PERSON, self::COUNTRY, 'citizen', 'Bürger von', 'hat Bürger'],
            [self::ORGANIZATION, self::LOCATION, 'based', 'ansässig in', 'beherbergt'],
            [self::OBJECT, self::LOCATION, 'stored', 'gelagert in', 'enthält'],
            [self::LOCATION, self::OBJECT, 'contains', 'enthält', 'gelagert in'],
            [self::LOCATION, self::PERSON, 'shelter', 'bietet Unterschlupf für', 'findet Unterschlupf in'],
            [self::LOCATION, self::ORGANIZATION, 'serves', 'dient als Stützpunkt für', 'nutzt als Stützpunkt'],
            [self::LOCATION, self::ORGANIZATION, 'disguises', 'getarnt als', 'tarnt sich als'],

            // Story & Quest Relations
            [self::QUEST, self::QUEST, 'prerequisite', 'Voraussetzung für', 'benötigt'],
            [self::QUEST, self::QUEST, 'parallel', 'parallel zu', 'parallel zu'],
            [self::QUEST, self::QUEST, 'alternative', 'Alternative zu', 'Alternative zu'],
            [self::QUEST, self::QUEST, 'chain', 'führt zu', 'folgt auf'],
            [self::QUEST, self::QUEST, 'sidequest', 'hat Nebenquest', 'Nebenquest von'],
            [self::QUEST, self::QUEST, 'blocks', 'blockiert', 'blockiert von'],
            [self::QUEST, self::QUEST, 'hints', 'gibt Hinweis auf', 'angedeutet durch'],
            [self::QUEST, self::ORGANIZATION, 'reputation', 'verbessert Ruf bei', 'Rufquest von'],
            [self::QUEST, self::ORGANIZATION, 'initiation', 'Aufnahmeprüfung für', 'prüft durch'],
            [self::QUEST, self::FACTION, 'faction_quest', 'steigert Ansehen bei', 'vergibt Quest'],
            [self::QUEST, self::RITUAL, 'ritual_quest', 'ermöglicht Ritual', 'benötigt Quest'],
            [self::QUEST, self::SPELL, 'unlocks_spell', 'schaltet Zauber frei', 'freigeschaltet durch'],
            [self::QUEST, self::ARTIFACT, 'assembles', 'stellt her', 'hergestellt in'],
            [self::CAMPAIGN, self::QUEST, 'contains', 'enthält Quest', 'Teil von Kampagne'],
            [self::CAMPAIGN, self::QUEST, 'main_quest', 'Hauptquest', 'Teil der Kampagne'],
            [self::CAMPAIGN, self::QUEST, 'side_content', 'optionaler Inhalt', 'optional in'],
            [self::CAMPAIGN, self::QUEST, 'hidden', 'versteckte Quest', 'versteckt in'],
            [self::QUEST, self::EVENT, 'triggers', 'löst aus', 'ausgelöst durch'],

            // Event Relations
            [self::EVENT, self::EVENT, 'precedes', 'geht voraus', 'folgt auf'],
            [self::EVENT, self::EVENT, 'causes', 'verursacht', 'verursacht durch'],
            [self::EVENT, self::LOCATION, 'happens', 'findet statt in', 'Schauplatz von'],
            [self::EVENT, self::PERSON, 'involves', 'involviert', 'beteiligt an'],
            [self::EVENT, self::ORGANIZATION, 'involves', 'involviert', 'beteiligt an'],
            [self::EVENT, self::ORGANIZATION, 'executed', 'durchgeführt von', 'führte durch'],
            [self::EVENT, self::PERSON, 'witnessed', 'beobachtet von', 'beobachtete'],

            // Campaign Relations
            [self::CAMPAIGN, self::CAMPAIGN, 'sequel', 'Vorläufer von', 'Fortsetzung von'],
            [self::CAMPAIGN, self::LOCATION, 'setting', 'spielt in', 'Schauplatz von'],
            [self::CAMPAIGN, self::ORGANIZATION, 'features', 'handelt von', 'erscheint in'],
            [self::CAMPAIGN, self::PERSON, 'features', 'handelt von', 'erscheint in'],

            // Location Story Context
            [self::QUEST, self::LOCATION, 'starts', 'beginnt in', 'Startpunkt von'],
            [self::QUEST, self::LOCATION, 'ends', 'endet in', 'Endpunkt von'],
            [self::QUEST, self::LOCATION, 'visits', 'führt durch', 'Teil von Quest'],

            // Character Quest Relations
            [self::PERSON, self::QUEST, 'gives', 'vergibt', 'vergeben von'],
            [self::PERSON, self::QUEST, 'involved', 'beteiligt an', 'involviert'],
            [self::ORGANIZATION, self::QUEST, 'gives', 'vergibt', 'vergeben von'],

            // Object Relations
            [self::QUEST, self::OBJECT, 'requires', 'benötigt', 'benötigt für'],
            [self::QUEST, self::OBJECT, 'rewards', 'belohnt mit', 'Belohnung für'],
            [self::EVENT, self::OBJECT, 'features', 'beinhaltet', 'verwendet in'],
            [self::OBJECT, self::LOCATION, 'located', 'befindet sich in', 'beherbergt'],
            [self::OBJECT, self::PERSON, 'taken', 'entwendet von', 'entwendete'],
            [self::OBJECT, self::ORGANIZATION, 'trafficked', 'gehandelt von', 'handelt mit'],
            [self::OBJECT, self::OBJECT, 'imitation', 'Imitation von', 'imitiert von'],

            // Other Relations
            [self::OTHER, self::LOCATION, 'located', 'befindet sich in', 'enthält'],
            [self::OTHER, self::EVENT, 'affects', 'beeinflusst', 'beeinflusst von'],
            [self::OTHER, self::QUEST, 'affects', 'beeinflusst', 'beeinflusst von'],
            [self::OTHER, self::CAMPAIGN, 'affects', 'beeinflusst', 'beeinflusst von'],
            [self::OTHER, self::OBJECT, 'affects', 'beeinflusst', 'beeinflusst von'],

            // Military & Combat Relations
            [self::ORGANIZATION, self::ORGANIZATION, 'military_alliance', 'Militärverbündeter von', 'Militärverbündeter mit'],
            [self::ORGANIZATION, self::PERSON, 'commands', 'befehligt', 'dient unter'],
            [self::PERSON, self::PERSON, 'squire', 'Knappe von', 'Ritter von'],
            [self::PERSON, self::ORGANIZATION, 'member_military', 'Mitglied der Armee von', 'hat Soldat'],

            // Religious & Faith Relations
            [self::ORGANIZATION, self::ORGANIZATION, 'religious_alliance', 'verbündet im Glauben mit', 'verbündet im Glauben mit'],
            [self::PERSON, self::ORGANIZATION, 'worships', 'betet in', 'hat Gläubigen'],
            [self::PERSON, self::PERSON, 'religious_mentor', 'spiritueller Mentor von', 'spiritueller Schüler von'],
            [self::LOCATION, self::ORGANIZATION, 'sacred_site', 'heiliger Ort von', 'hat heiligen Ort'],
            [self::DEITY, self::LANDMARK, 'manifests', 'manifestiert sich in', 'Manifestationsort von'],

            // Magical Relations
            [self::PERSON, self::PERSON, 'apprentice', 'Meister von', 'Lehrling von'],
            [self::OBJECT, self::PERSON, 'bound', 'gebunden an', 'gebunden mit'],
            [self::LOCATION, self::LOCATION, 'ley_line', 'magisch verbunden mit', 'magisch verbunden mit'],
            [self::OBJECT, self::OBJECT, 'resonates', 'resoniert mit', 'resoniert mit'],
            [self::SPELL, self::LANDMARK, 'resonates', 'resoniert mit', 'resoniert mit'],

            // Knowledge & Learning
            [self::ORGANIZATION, self::ORGANIZATION, 'knowledge_share', 'teilt Wissen mit', 'teilt Wissen mit'],
            [self::PERSON, self::ORGANIZATION, 'studies', 'studiert bei', 'lehrt'],
            [self::OBJECT, self::ORGANIZATION, 'archived', 'archiviert in', 'archiviert'],
            [self::LOCATION, self::ORGANIZATION, 'research_site', 'Forschungsort von', 'forscht in'],

            // Criminal & Underground
            [self::ORGANIZATION, self::ORGANIZATION, 'criminal_alliance', 'kriminell verbündet mit', 'kriminell verbündet mit'],
            [self::PERSON, self::ORGANIZATION, 'smuggles', 'schmuggelt für', 'nutzt Schmuggler'],
            [self::LOCATION, self::ORGANIZATION, 'hideout', 'Versteck von', 'versteckt sich in'],
            [self::PERSON, self::PERSON, 'blackmails', 'erpresst', 'erpresst von'],
            [self::ORGANIZATION, self::ORGANIZATION, 'criminal_alliance', 'kriminell verbündet mit', 'kriminell verbündet mit'],
            [self::ORGANIZATION, self::ORGANIZATION, 'rival_gang', 'rivalisiert mit', 'rivalisiert mit'],
            [self::ORGANIZATION, self::LOCATION, 'territory', 'kontrolliert Territorium', 'kontrolliert von'],
            [self::ORGANIZATION, self::SETTLEMENT, 'criminal_influence', 'hat kriminellen Einfluss in', 'beeinflusst von'],
            [self::ORGANIZATION, self::PERSON, 'extorts', 'erpresst', 'erpresst von'],
            [self::ORGANIZATION, self::PERSON, 'protects', 'gewährt Schutz für', 'unter Schutz von'],

            [self::PERSON, self::PERSON, 'blackmails', 'erpresst', 'erpresst von'],
            [self::PERSON, self::PERSON, 'fence', 'Hehler für', 'verkauft über'],
            [self::PERSON, self::PERSON, 'informant', 'Informant für', 'hat Informanten'],
            [self::PERSON, self::ORGANIZATION, 'smuggles', 'schmuggelt für', 'nutzt Schmuggler'],
            [self::PERSON, self::ORGANIZATION, 'infiltrates', 'infiltriert', 'infiltriert von'],

            [self::LOCATION, self::ORGANIZATION, 'hideout', 'Versteck von', 'versteckt sich in'],
            [self::LOCATION, self::ORGANIZATION, 'front', 'Tarnung für', 'getarnt als'],
            [self::LOCATION, self::OBJECT, 'contraband', 'versteckt Schmuggelware', 'versteckt in'],
            [self::LOCATION, self::PERSON, 'safehouse', 'Unterschlupf für', 'versteckt sich in'],

            [self::OBJECT, self::PERSON, 'stolen', 'gestohlen von', 'stahl'],
            [self::OBJECT, self::ORGANIZATION, 'smuggled', 'geschmuggelt von', 'schmuggelt'],
            [self::OBJECT, self::OBJECT, 'counterfeit', 'Fälschung von', 'gefälscht als'],

            [self::EVENT, self::ORGANIZATION, 'heist', 'ausgeführt von', 'führte aus'],
            [self::EVENT, self::PERSON, 'crime_witness', 'bezeugt von', 'bezeugte'],

            // Trade & Crafting
            [self::PERSON, self::ORGANIZATION, 'supplies', 'beliefert', 'beliefert von'],
            [self::OBJECT, self::PERSON, 'crafted', 'hergestellt von', 'erschuf'],
            [self::LOCATION, self::OBJECT, 'resource', 'Quelle von', 'gewonnen aus'],
            [self::ORGANIZATION, self::LOCATION, 'controls_trade', 'kontrolliert Handel in', 'Handel kontrolliert von'],

            // Social Status & Hierarchy
            [self::PERSON, self::PERSON, 'serves', 'dient', 'wird bedient von'],
            [self::PERSON, self::ORGANIZATION, 'noble', 'Adeliger von', 'hat Adeligen'],
            [self::ORGANIZATION, self::PERSON, 'patron', 'Schirmherr von', 'Schützling von'],
            [self::PERSON, self::ORGANIZATION, 'founder', 'gründete', 'gegründet von'],

            // Faction & Guild Relations
            [self::ORGANIZATION, self::ORGANIZATION, 'guild_alliance', 'Gildenallianz mit', 'Gildenallianz mit'],
            [self::PERSON, self::ORGANIZATION, 'guild_master', 'Gildenmeister von', 'geleitet von'],
            [self::LOCATION, self::ORGANIZATION, 'guild_hall', 'Gildenhalle von', 'hat Gildenhalle'],
            [self::OBJECT, self::ORGANIZATION, 'guild_artifact', 'Gildenartefakt von', 'besitzt Artefakt'],

            // Mystical & Prophecy
            [self::PERSON, self::EVENT, 'prophesied', 'prophezeit', 'prophezeit von'],
            [self::OBJECT, self::EVENT, 'catalyst', 'Katalysator für', 'ausgelöst durch'],
            [self::LOCATION, self::EVENT, 'destined', 'Schicksalsort von', 'bestimmt für'],
            [self::ORGANIZATION, self::EVENT, 'foretold', 'vorhergesagt von', 'sagt vorher'],

            // Planar & Divine
            [self::LOCATION, self::LOCATION, 'portal', 'Portal zu', 'Portal von'],
            [self::OBJECT, self::LOCATION, 'planar_anchor', 'Planarverankerung in', 'verankert'],
            [self::ORGANIZATION, self::LOCATION, 'planar_influence', 'beeinflusst Plan', 'beeinflusst von'],
            [self::PERSON, self::LOCATION, 'planar_origin', 'stammt aus', 'Heimat von'],

            // Quest & Adventure Specific
            [self::OBJECT, self::QUEST, 'quest_item', 'Questgegenstand für', 'benötigt'],
            [self::LOCATION, self::QUEST, 'quest_location', 'Questort für', 'findet statt in'],
            [self::ORGANIZATION, self::QUEST, 'quest_faction', 'Questfraktion für', 'involviert'],
            [self::PERSON, self::QUEST, 'quest_giver', 'Questgeber für', 'vergeben von'],

            // Divine & Religious Relations
            [self::DEITY, self::DEITY, 'pantheon', 'im Pantheon mit', 'im Pantheon mit'],
            [self::DEITY, self::ORGANIZATION, 'church', 'verehrt durch', 'Kirche von'],
            [self::DEITY, self::PERSON, 'patron_deity', 'Schutzpatron von', 'gesegnet von'],
            [self::DEITY, self::LOCATION, 'holy_site', 'heiliger Ort von', 'geweiht an'],
            [self::DEITY, self::ARTIFACT, 'divine_artifact', 'erschuf', 'erschaffen von'],
            [self::RELIGION, self::RITUAL, 'prescribes', 'schreibt vor', 'vorgeschrieben von'],
            [self::RELIGION, self::DEITY, 'worships', 'verehrt', 'verehrt durch'],
            [self::RELIGION, self::RELIGION, 'heresy', 'betrachtet als Häresie', 'betrachtet als Häresie'],
            [self::RITUAL, self::SPELL, 'invokes', 'ruft herbei', 'herbeigerufen durch'],
            [self::RITUAL, self::OBJECT, 'requires', 'benötigt', 'benötigt für'],
            [self::LOCATION, self::RITUAL, 'ritual_site', 'Ritualort für', 'durchgeführt in'],

            // Creature Relations
            [self::CREATURE, self::CREATURE, 'pack', 'führt Rudel von', 'folgt'],
            [self::CREATURE, self::LOCATION, 'habitat', 'lebt in', 'Heimat von'],
            [self::CREATURE, self::PERSON, 'familiar', 'Vertrauter von', 'gebunden an'],
            [self::CREATURE, self::DEITY, 'sacred', 'heiliges Tier von', 'hat heiliges Tier'],
            [self::CREATURE, self::ORGANIZATION, 'guards', 'bewacht', 'bewacht von'],
            [self::CREATURE, self::CREATURE, 'predator', 'jagt', 'gejagt von'],
            [self::CREATURE, self::CREATURE, 'symbiotic', 'lebt symbiotisch mit', 'lebt symbiotisch mit'],
            [self::CREATURE, self::LOCATION, 'migration', 'wandert zu', 'Wanderziel von'],

            // Settlement Relations
            [self::SETTLEMENT, self::SETTLEMENT, 'trade_route', 'Handelsroute zu', 'Handelsroute von'],
            [self::SETTLEMENT, self::FACTION, 'controls', 'kontrolliert von', 'kontrolliert'],
            [self::SETTLEMENT, self::PERSON, 'rules', 'regiert von', 'regiert'],
            [self::SETTLEMENT, self::CREATURE, 'threatened', 'bedroht von', 'bedroht'],

            // Magical Relations
            [self::SPELL, self::PERSON, 'created', 'erschaffen von', 'entwickelte'],
            [self::SPELL, self::ARTIFACT, 'stored', 'gespeichert in', 'enthält'],
            [self::SPELL, self::ORGANIZATION, 'teaches', 'gelehrt von', 'lehrt'],
            [self::SPELL, self::LOCATION, 'affected', 'wirkt auf', 'beeinflusst von'],

            // Artifact Relations
            [self::ARTIFACT, self::DEITY, 'blessed', 'gesegnet von', 'segnete'],
            [self::ARTIFACT, self::PERSON, 'wielded', 'geführt von', 'führt'],
            [self::ARTIFACT, self::LOCATION, 'hidden', 'versteckt in', 'verbirgt'],
            [self::ARTIFACT, self::CREATURE, 'guarded', 'bewacht von', 'bewacht'],

            // Faction Relations
            [self::FACTION, self::FACTION, 'alliance', 'verbündet mit', 'verbündet mit'],
            [self::FACTION, self::PERSON, 'leads', 'geführt von', 'führt'],
            [self::FACTION, self::ARTIFACT, 'possesses', 'besitzt', 'gehört'],
            [self::FACTION, self::LOCATION, 'controls', 'kontrolliert', 'kontrolliert von'],
            [self::FACTION, self::DEITY, 'worships', 'verehrt', 'verehrt von'],

            // Landmark Relations
            [self::LANDMARK, self::DEITY, 'consecrated', 'geweiht an', 'gesegnet'],
            [self::LANDMARK, self::EVENT, 'witnessed', 'Zeuge von', 'geschehen bei'],
            [self::LANDMARK, self::SPELL, 'empowered', 'verstärkt', 'verstärkt durch'],
            [self::LANDMARK, self::CREATURE, 'attracts', 'zieht an', 'angezogen von'],

            // Campaign Relations (expanded)
            [self::CAMPAIGN, self::CAMPAIGN, 'sequel', 'Vorläufer von', 'Fortsetzung von'],
            [self::CAMPAIGN, self::CAMPAIGN, 'concurrent', 'läuft parallel zu', 'läuft parallel zu'],
            [self::CAMPAIGN, self::CAMPAIGN, 'spinoff', 'Ableger von', 'hat Ableger'],
            [self::CAMPAIGN, self::CAMPAIGN, 'alternative_timeline', 'Alternative zu', 'Alternative zu'],

            [self::CAMPAIGN, self::QUEST, 'main_quest', 'Hauptquest', 'Teil der Kampagne'],
            [self::CAMPAIGN, self::QUEST, 'side_content', 'optionaler Inhalt', 'optional in'],
            [self::CAMPAIGN, self::QUEST, 'hidden', 'versteckte Quest', 'versteckt in'],

            [self::CAMPAIGN, self::EVENT, 'pivotal_event', 'Schlüsselereignis', 'Schlüsselereignis in'],
            [self::CAMPAIGN, self::EVENT, 'background_event', 'Hintergrundereignis', 'Hintergrund von'],
            [self::CAMPAIGN, self::EVENT, 'finale', 'endet mit', 'Finale von'],

            [self::CAMPAIGN, self::DEITY, 'divine_intervention', 'göttlicher Eingriff von', 'greift ein in'],
            [self::CAMPAIGN, self::ARTIFACT, 'artifact_story', 'handelt von', 'zentral in'],

            [self::CAMPAIGN, self::FACTION, 'faction_focus', 'fokussiert auf', 'Hauptfokus von'],
            [self::CAMPAIGN, self::SETTLEMENT, 'home_base', 'Hauptquartier in', 'Basis für'],
            [self::CAMPAIGN, self::ORGANIZATION, 'antagonist', 'bekämpft', 'Gegenspieler in'],

            // Story Progression Relations
            [self::EVENT, self::QUEST, 'unlocks', 'schaltet frei', 'freigeschaltet durch'],
            [self::EVENT, self::CAMPAIGN, 'derails', 'verändert Verlauf von', 'verändert durch'],
            [self::EVENT, self::QUEST, 'fails', 'lässt scheitern', 'gescheitert durch'],
            [self::EVENT, self::CAMPAIGN, 'redirects', 'lenkt um', 'umgelenkt durch'],

            // Organization Influence Relations
            [self::ORGANIZATION, self::SETTLEMENT, 'economic_power', 'hat wirtschaftliche Macht in', 'wirtschaftlich abhängig von'],
            [self::ORGANIZATION, self::SETTLEMENT, 'political_power', 'hat politischen Einfluss in', 'politisch beeinflusst von'],
            [self::ORGANIZATION, self::REGION, 'dominates', 'dominiert', 'dominiert von'],
            [self::ORGANIZATION, self::ORGANIZATION, 'puppet', 'kontrolliert', 'kontrolliert von'],

            // Trading & Commerce Relations
            [self::SETTLEMENT, self::SETTLEMENT, 'major_trade', 'wichtiger Handelspartner von', 'wichtiger Handelspartner mit'],
            [self::SETTLEMENT, self::SETTLEMENT, 'black_market', 'Schwarzmarktroute zu', 'Schwarzmarktroute von'],
            [self::SETTLEMENT, self::ORGANIZATION, 'market_control', 'Markt kontrolliert von', 'kontrolliert Markt in'],
            [self::SETTLEMENT, self::ORGANIZATION, 'merchant_guild', 'Handelsgilde ansässig in', 'hat Niederlassung in'],

            [self::ORGANIZATION, self::SETTLEMENT, 'monopoly', 'hat Handelsmonopol in', 'Handel monopolisiert von'],
            [self::ORGANIZATION, self::REGION, 'trade_rights', 'hat Handelsrechte in', 'Handelsrechte vergeben an'],
            [self::ORGANIZATION, self::ORGANIZATION, 'trade_cartel', 'Handelskartell mit', 'Handelskartell mit'],
            [self::ORGANIZATION, self::OBJECT, 'exclusive_trade', 'exklusiver Händler für', 'exklusiv gehandelt von'],

            [self::LOCATION, self::OBJECT, 'speciality', 'bekannt für', 'Spezialität von'],
            [self::LOCATION, self::ORGANIZATION, 'trade_post', 'Handelsposten von', 'unterhält Handelsposten in'],
            [self::LOCATION, self::LOCATION, 'trade_stop', 'Handelsstation für', 'nutzt Handelsstation'],

            [self::PERSON, self::ORGANIZATION, 'merchant', 'Händler für', 'beschäftigt als Händler'],
            [self::PERSON, self::SETTLEMENT, 'market_rights', 'hat Marktrecht in', 'gewährt Marktrecht an'],
            [self::PERSON, self::OBJECT, 'specialized_trader', 'spezialisiert auf Handel mit', 'speziell gehandelt von'],

            [self::OBJECT, self::SETTLEMENT, 'main_export', 'Hauptexport von', 'exportiert hauptsächlich'],
            [self::OBJECT, self::SETTLEMENT, 'main_import', 'Hauptimport von', 'importiert hauptsächlich'],
            [self::OBJECT, self::LOCATION, 'trade_good', 'gehandelt in', 'handelt mit'],

            [self::FACTION, self::SETTLEMENT, 'controls_prices', 'kontrolliert Preise in', 'Preise kontrolliert von'],
            [self::FACTION, self::ORGANIZATION, 'trade_alliance', 'Handelsallianz mit', 'Handelsallianz mit'],
            [self::FACTION, self::OBJECT, 'regulates_trade', 'reguliert Handel mit', 'Handel reguliert von'],

            // Weapon Relations
            [self::WEAPON, self::PERSON, 'wielded', 'geführt von', 'führt'],
            [self::WEAPON, self::ORGANIZATION, 'standard_issue', 'Standardausrüstung von', 'rüstet aus mit'],

            // Armor Relations
            [self::ARMOR, self::PERSON, 'worn', 'getragen von', 'trägt'],
            [self::ARMOR, self::ORGANIZATION, 'uniform', 'Uniform von', 'trägt als Uniform'],

            // Tool Relations
            [self::TOOL, self::PERSON, 'used', 'benutzt von', 'benutzt'],
            [self::TOOL, self::ORGANIZATION, 'equipment', 'Ausrüstung von', 'verwendet'],

            // Document Relations
            [self::DOCUMENT, self::PERSON, 'authored', 'verfasst von', 'verfasste'],
            [self::DOCUMENT, self::ORGANIZATION, 'archived', 'archiviert von', 'archiviert'],
            [self::DOCUMENT, self::EVENT, 'records', 'dokumentiert', 'dokumentiert in'],

            // Container Relations
            [self::CONTAINER, self::OBJECT, 'contains', 'enthält', 'gelagert in'],
            [self::CONTAINER, self::PERSON, 'secured', 'gesichert von', 'sichert'],

            // Valuable Relations
            [self::VALUABLE, self::PERSON, 'collected', 'gesammelt von', 'sammelt'],
            [self::VALUABLE, self::ORGANIZATION, 'traded', 'gehandelt von', 'handelt mit'],

            // Consumable Relations
            [self::CONSUMABLE, self::PERSON, 'uses', 'verwendet von', 'verwendet'],
            [self::CONSUMABLE, self::ORGANIZATION, 'produces', 'hergestellt von', 'stellt her'],
        ];

        // Inherit base object relations for all object subtypes
        /** @var list<array{0: ItemType, 1: ItemType, 2: string, 3: string, 4: string}> $inheritedRelations */
        $inheritedRelations = [];
        foreach ($baseRelations as $relation) {
            if ($relation[0] !== self::OBJECT) {
                continue;
            }

            $objectSubtypes = self::getObjectSubTypes();
            foreach ($objectSubtypes as $subtype) {
                $inheritedRelations[] = [$subtype, $relation[1], $relation[2], $relation[3], $relation[4]];
            }
        }

        return array_merge($baseRelations, $inheritedRelations); // @phpstan-ignore return.type
    }

    /** @return ItemType[] */
    public static function getObjectSubTypes(): array
    {
        return [
            self::ARTIFACT,
            self::WEAPON,
            self::ARMOR,
            self::TOOL,
            self::DOCUMENT,
            self::CONTAINER,
            self::VALUABLE,
            self::CONSUMABLE,
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

            if ($targetType !== self::OBJECT) {
                continue;
            }

            foreach (self::getObjectSubTypes() as $subType) {
                $relationTypes[$sourceType->value][$subType->value][$relationType] = $sourceLabel;
                $relationTypes[$subType->value][$sourceType->value][$relationType] = $targetLabel;
            }
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
