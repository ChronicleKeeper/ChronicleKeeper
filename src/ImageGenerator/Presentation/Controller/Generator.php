<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Presentation\Controller;

use ChronicleKeeper\ImageGenerator\Application\Service\OpenAIGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/image_generator', name: 'image_generator')]
final class Generator extends AbstractController
{
    public function __construct(
        private readonly OpenAIGenerator $generator,
    )
    {

    }

    public function __invoke(Request $request): Response
    {
        $images = [];
        if ($request->isMethod(Request::METHOD_POST)) {
            $prompt = $request->get('prompt');
            $images = $this->generator->generate($prompt);
        }

        return $this->render('image_generator/generator.html.twig', ['images' => $images]);
    }
}
