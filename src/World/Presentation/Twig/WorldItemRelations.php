<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Twig;

use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Application\Query\FindRelationsOfItem;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\Relation;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('World:ItemRelations', template: 'components/world/item_relations.html.twig')]
class WorldItemRelations
{
    public Item $item;

    public function __construct(private readonly QueryService $queryService)
    {
    }

    /** @return Relation[] */
    public function getRelations(): array
    {
        return $this->queryService->query(new FindRelationsOfItem($this->item));
    }

    public function getRelationLabel(Item $toItem, string $relationType): string
    {
        return $this->item->getType()->getRelationLabelTo($toItem->getType(), $relationType);
    }
}
