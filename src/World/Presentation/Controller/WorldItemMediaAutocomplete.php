<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Controller;

use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Application\Query\FindAllReferencableMedia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/world/item/media/autocomplete',
    name: 'world_item_media_autocomplete',
    methods: ['GET'],
)]
final class WorldItemMediaAutocomplete extends AbstractController
{
    public function __construct(private readonly QueryService $queryService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->get('query', '');
        if ($search === '') {
            return new JsonResponse(['results' => []]);
        }

        return new JsonResponse([
            'results' => $this->queryService->query(new FindAllReferencableMedia($search)),
        ]);
    }
}
