<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\Presentation\Controller\Image;

use DZunke\NovDoc\Library\Domain\Entity\Image;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemImageRepository;
use DZunke\NovDoc\Library\Presentation\Form\ImageType;
use DZunke\NovDoc\Shared\Presentation\FlashMessages\Alert;
use DZunke\NovDoc\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

#[Route(
    '/library/image/{image}/edit',
    name: 'library_image_edit',
    requirements: ['image' => Requirement::UUID],
)]
class ImageEdit extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly FormFactoryInterface $formFactory,
        private readonly FilesystemImageRepository $imageRepository,
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(Request $request, Image $image): Response
    {
        $form = $this->formFactory->create(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->imageRepository->store($image);

            $this->addFlashMessage(
                $request,
                Alert::SUCCESS,
                'Das Bild wurde bearbeitet, damit die Änderungen in der Suche aktiv sind muss der Index aktualisiert werden.',
            );

            return new RedirectResponse($this->router->generate('library', ['directory' => $image->directory->id]));
        }

        return new Response($this->environment->render(
            'library/image_edit.html.twig',
            ['image' => $image, 'form' => $form->createView()],
        ));
    }
}
