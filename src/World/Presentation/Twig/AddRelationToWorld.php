<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Twig;

use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

use function strcmp;
use function uasort;

#[AsTwigComponent('World:AddRelationToWorld', template: 'components/world/add_relation_to_world.html.twig')]
class AddRelationToWorld
{
    public string $itemId;
    public ItemType $itemType;

    /** @return array<string, array<string, array<string, string>>> */
    public function getAvailableRelations(): array
    {
        $relationTypes = ItemType::getRelationTypesTo();

        foreach ($relationTypes as &$targetTypes) {
            foreach ($targetTypes as &$relations) {
                uasort($relations, static fn (string $a, string $b) => strcmp($a, $b));
            }
        }

        return $relationTypes;
    }
}
