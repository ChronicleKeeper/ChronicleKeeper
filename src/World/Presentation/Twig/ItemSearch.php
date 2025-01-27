<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Twig;

use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Application\Query\SearchWorldItems;
use ChronicleKeeper\World\Application\Query\SearchWorldItemsCount;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function ceil;
use function floor;
use function max;
use function min;
use function range;

#[AsLiveComponent('World:ItemSearch', template: 'components/world/item_search.html.twig')]
final class ItemSearch
{
    public const int ITEMS_PER_PAGE   = 15;
    private const int MAX_PAGES_SHOWN = 5;
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $search = '';

    #[LiveProp(writable: true)]
    public string $type = '';

    #[LiveProp(writable: true)]
    public int $page = 1;

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
                offset: ($this->page - 1) * self::ITEMS_PER_PAGE,
                limit: self::ITEMS_PER_PAGE,
            ),
        );
    }

    public function getTotalItems(): int
    {
        return $this->queryService->query(
            new SearchWorldItemsCount(
                search: $this->search,
                type: $this->type,
            ),
        );
    }

    public function getTotalPages(int|null $totalItems = null): int
    {
        return (int) ceil(($totalItems ?? $this->getTotalItems()) / self::ITEMS_PER_PAGE);
    }

    public function getItemsPerPage(): int
    {
        return self::ITEMS_PER_PAGE;
    }

    /** @return non-empty-list<int|null>|list<int<min, 5>> */
    public function getPageRange(int|null $totalPages = null): array
    {
        $totalPages ??= $this->getTotalPages();
        if ($totalPages <= self::MAX_PAGES_SHOWN) {
            return range(1, $totalPages);
        }

        $range   = [];
        $halfMax = (int) floor(self::MAX_PAGES_SHOWN / 2);

        // Always show first page
        $range[] = 1;

        // Calculate start and end of the center range
        $start = max(2, $this->page - $halfMax);
        $end   = min($totalPages - 1, $this->page + $halfMax);

        // Add ellipsis after first page if needed
        if ($start > 2) {
            $range[] = null;
        }

        // Add center range
        for ($i = $start; $i <= $end; $i++) {
            $range[] = $i;
        }

        // Add ellipsis before last page if needed
        if ($end < $totalPages - 1) {
            $range[] = null;
        }

        $range[] = $totalPages;

        return $range;
    }

    #[LiveAction]
    public function updatePage(#[LiveArg]
    int $page,): void
    {
        $this->page = max(1, min($page, $this->getTotalPages()));
    }
}
