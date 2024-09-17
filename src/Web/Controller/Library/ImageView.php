<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Library;

use DZunke\NovDoc\Domain\Library\Image\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Twig\Environment;

#[Route(
    '/library/image/{image}',
    name: 'library_image_view',
    requirements: ['image' => Requirement::UUID],
)]
class ImageView
{
    public function __construct(
        private readonly Environment $environment,
    ) {
    }

    public function __invoke(Request $request, Image $image): Response
    {
        return new Response($this->environment->render(
            'library/image_view.html.twig',
            ['image' => $image],
        ));
    }
}