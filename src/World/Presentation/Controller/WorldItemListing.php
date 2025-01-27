<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Controller;

use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Application\Query\SearchWorldItems;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/world', name: 'world_item_listing')]
final class WorldItemListing extends AbstractController
{
    public function __construct(private readonly QueryService $queryService)
    {
    }

    public function __invoke(): Response
    {
        return $this->render(
            'world/item_listing.html.twig',
            [
                'items' => $this->queryService->query(new SearchWorldItems()),
                'item_types' => ItemType::cases(),
            ],
        );
    }
}
