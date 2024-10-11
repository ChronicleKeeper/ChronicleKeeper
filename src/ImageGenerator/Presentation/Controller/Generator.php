<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Presentation\Controller;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/image_generator/{generatorRequest}/generator',
    name: 'image_generator_generator',
    requirements: ['generatorRequest' => Requirement::UUID],
)]
final class Generator extends AbstractController
{
    public function __invoke(Request $request, GeneratorRequest $generatorRequest): Response
    {
        return $this->render(
            'image_generator/generator.html.twig',
            ['request' => $generatorRequest],
        );
    }
}
