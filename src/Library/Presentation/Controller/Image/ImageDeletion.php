<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller\Image;

use ChronicleKeeper\Image\Application\Command\DeleteImage;
use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(Request $request, Image $image): Response
    {
        if ($request->get('confirm', 0) === 0) {
            $this->addFlashMessage(
                $request,
                Alert::WARNING,
                'Das Löschen des Bildes "' . $image->getTitle() . '" muss erst bestätigt werden!',
            );

            return new RedirectResponse($this->router->generate('library', ['directory' => $image->getDirectory()->getId()]));
        }

        $this->bus->dispatch(new DeleteImage($image));

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Das Bild "' . $image->getTitle() . '" wurde erfolgreich gelöscht.',
        );

        return new RedirectResponse($this->router->generate('library', ['directory' => $image->getDirectory()->getId()]));
    }
}
