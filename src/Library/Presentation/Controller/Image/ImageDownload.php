<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller\Image;

use ChronicleKeeper\Image\Domain\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Routing\RouterInterface;

use function base64_decode;
use function strlen;

#[Route(
    '/library/image/{image}/download',
    name: 'library_image_download',
    requirements: ['image' => Requirement::UUID],
)]
class ImageDownload extends AbstractController
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(Image $image): Response
    {
        $decodedImage = base64_decode($image->getEncodedImage(), true);
        if ($decodedImage === false) {
            return new RedirectResponse($this->router->generate('library_image_view', ['image' => $image->getId()]));
        }

        $response = new Response($decodedImage);
        $response->headers->set('Content-Type', $image->getMimeType());
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $image->getTitle() . '"');
        $response->headers->set('Content-length', (string) strlen($decodedImage));

        return $response;
    }
}
