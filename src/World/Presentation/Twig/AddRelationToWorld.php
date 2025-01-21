<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Twig;

use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('World:AddRelationToWorld', template: 'components/world/add_relation_to_world.html.twig')]
class AddRelationToWorld
{
    public string $itemId;
    public ItemType $itemType;

    /** @return array<string, array<string, array<string, string>>> */
    public function getAvailableRelations(): array
    {
        return ItemType::getRelationTypesTo();
    }
}
