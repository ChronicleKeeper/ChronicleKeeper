<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Controller;

use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Application\Query\SearchWorldItems;
use ChronicleKeeper\World\Domain\Entity\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Routing\Requirement\Requirement;

use function array_map;

#[Route(
    '/world/item/{id}/autocomplete',
    name: 'world_item_relation_autocomplete',
    methods: ['GET'],
    requirements: ['id' => Requirement::UUID],
)]
final class WorldItemAutocomplete extends AbstractController
{
    public function __construct(private readonly QueryService $queryService)
    {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $search = $request->get('query', '');
        if ($search === '') {
            return new JsonResponse(['results' => []]);
        }

        return new JsonResponse([
            'results' => $this->formatResults($this->queryService->query(
                new SearchWorldItems(
                    search: $search,
                    exclude: [$id],
                ),
            )),
        ]);
    }

    /**
     * @param Item[] $items
     *
     * @return array{value: string, text: string, type: string}[]
     */
    private function formatResults(array $items): array
    {
        return array_map(
            static fn (Item $item) => [
                'value' => $item->getId(),
                'text' => $item->getName(),
                'type' => $item->getType()->value,
            ],
            $items,
        );
    }
}
