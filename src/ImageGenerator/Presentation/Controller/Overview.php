<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Presentation\Controller;

use ChronicleKeeper\ImageGenerator\Application\Query\FindAllGeneratorRequests;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/image_generator', name: 'image_generator_overview')]
final class Overview extends AbstractController
{
    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->render(
            'image_generator/overview.html.twig',
            ['requests' => $this->queryService->query(new FindAllGeneratorRequests())],
        );
    }
}
