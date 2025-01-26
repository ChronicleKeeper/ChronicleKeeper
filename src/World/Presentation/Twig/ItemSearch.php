<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Twig;

use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Application\Query\SearchWorldItems;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('World:ItemSearch', template: 'components/world/item_search.html.twig')]
final class ItemSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $search = '';

    #[LiveProp(writable: true)]
    public string $type = '';

    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    /** @return ItemType[] */
    public function getItemTypes(): array
    {
        return ItemType::cases();
    }

    /** @return Item[] */
    public function getItems(): array
    {
        return $this->queryService->query(
            new SearchWorldItems(
                search: $this->search,
                type: $this->type,
            ),
        );
    }
}
