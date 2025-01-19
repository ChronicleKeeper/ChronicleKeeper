<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Controller;

use ChronicleKeeper\World\Domain\Entity\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/world/item/{id}',
    name: 'world_item_view',
    requirements: ['id' => Requirement::UUID],
)]
final class WorldItemView extends AbstractController
{
    public function __invoke(Item $item): Response
    {
        return $this->render('world/item_view.html.twig', ['item' => $item]);
    }
}
