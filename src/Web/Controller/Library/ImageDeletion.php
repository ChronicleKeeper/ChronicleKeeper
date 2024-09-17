<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Library;

use DZunke\NovDoc\Domain\Library\Image\Image;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemImageRepository;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemVectorImageRepository;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
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
class ImageDeletion
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
