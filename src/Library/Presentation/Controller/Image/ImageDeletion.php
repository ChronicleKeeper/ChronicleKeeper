<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\Presentation\Controller\Image;

use DZunke\NovDoc\Library\Domain\Entity\Image;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemImageRepository;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use DZunke\NovDoc\Shared\Presentation\FlashMessages\Alert;
use DZunke\NovDoc\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Routing\RouterInterface;

#[Route(
    '/library/image/{image}/delete',
    name: 'library_image_delete',
    requirements: ['image' => Requirement::UUID],
)]
class ImageDeletion extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly RouterInterface $router,
        private readonly FilesystemImageRepository $imageRepository,
        private readonly FilesystemVectorImageRepository $vectorImageRepository,
    ) {
    }

    public function __invoke(Request $request, Image $image): Response
    {
        if ($request->get('confirm', 0) === 0) {
            $this->addFlashMessage(
                $request,
                Alert::WARNING,
                'Das Löschen des Bildes "' . $image->title . '" muss erst bestätigt werden!',
            );

            return new RedirectResponse($this->router->generate('library', ['directory' => $image->directory->id]));
        }

        foreach ($this->vectorImageRepository->findAllByImageId($image->id) as $vectorImage) {
            $this->vectorImageRepository->remove($vectorImage);
        }

        $this->imageRepository->remove($image);

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Das Bild "' . $image->title . '" wurde erfolgreich gelöscht.',
        );

        return new RedirectResponse($this->router->generate('library', ['directory' => $image->directory->id]));
    }
}
